<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassGenerateAndDownloadLabels extends Action
{
    public $order;
    public $apimodel;
    public $consignmentmodel;

    /**
     * @var LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @var FileFactory
     */
    protected $fileFactory;
    protected $messageManager;
    protected $jsonHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Model\Api $apimodel,
        LabelGenerator $labelGenerator,
        FileFactory $fileFactory,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->order = $order;
        $this->apimodel = $apimodel;
        $this->labelGenerator = $labelGenerator;
        $this->fileFactory = $fileFactory;
        $this->consignmentmodel = $consignmentmodel;
        parent::__construct($context);
    }

    public function execute()
    {
        $getParams = $this->getRequest()->getParams();
        $ids = $getParams['order_consignment'];
        $consignments = [];
        if (!isset($ids)) {
            $this->messageManager->addError(__('Please select item(s)'));
            $this->_redirect('*/*/index');
            return true;
        } else {
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
            foreach ($ids as $key => $value) {
                $order_ids[] = explode('_', $value);
            }
            $ignoreLabels = [];
            foreach ($order_ids as $key1 => $value1) {
                $orderId = $value1[0];
                $consignmentNumber = $value1[1];
                $orderData = $this->order->load($orderId)->getData();
                if ($orderData['eparcel_shipping_id'] != '') {
                    $shipmentData[$key1]['shipmentId'] = $orderData['eparcel_shipping_id'];
                    $shipmentData[$key1]['consignment_id'] = $consignmentNumber;
                    $consignments[] = $consignmentNumber;
                } else {
                    $ignoreLabels[] = $consignmentNumber;
                }
            }

            if (!empty($ignoreLabels)) {
                $ignoreConsignmentIds = implode(",", $ignoreLabels);
                $message = __("Cannot Download Label for consignment(s) numbers " . $ignoreConsignmentIds . " Please Generate Shipment First!");
                $this->messageManager->addError($message);
            }
            if (isset($shipmentData)) {
                $finalDataToSend = ['shipment_id' => $shipmentData];

                $getResponse = $this->apimodel->massLabelGenerateAndDownload($finalDataToSend);
                if ($getResponse != null) {
                    $this->jsonHelper = $this->_objectManager->get('Magento\Framework\Json\Helper\Data');
                    $decodeResponse = $this->jsonHelper->jsonDecode($getResponse, true);

                    foreach ($decodeResponse as $key => $value) {
                        if ($key == 'status' && $value == 'success' || $key == 'url') {
                            if (is_array($value)) {
                                $consignmentsLabels = $value;
                            }
                        }
                    }
                    if (empty($consignmentsLabels)) {
                        $this->messageManager->addError("Cannot Download label. It might be available soon try after some time.");
                        $this->_redirect('*/*/index');
                        return true;
                    }
                    $finalArray = [];
                    foreach ($consignmentsLabels as $key => $value) {
                        $timestamp = time();
                        $date = date('Y-m-d H:i:s', $timestamp);
                        $labelUrl = '';
                        if (isset($value['label_url']) || isset($value['pdf_url'])) {
                            $labelUrl = (isset($value['label_url'])) ? $value['label_url'] : $value['pdf_url'];
                            /*$consignmentModel = $this->consignmentmodel->load($value['consignment_number'], 'consignment_number')
                                    ->setIsLabelCreated(1)
                                    ->setIsLabelPrinted(1)
                                    ->setModifyDate($date)
                                    ->save();
                            $orderId = $consignmentModel->getOrderId();
                            $orderToPrepare = $this->order->load($orderId);
                            $orderToPrepare->setIsLabelGenerated(1)
                                    ->setIsLabelPrinted(1)
                                    ->save();*/
                        }

                        if (in_array($labelUrl, $finalArray)) {
                            continue;
                        } else {
                            array_push($finalArray, $labelUrl);
                        }
                    }
                    foreach ($shipmentData as $data) {
                        $consignmentModel = $this->consignmentmodel->load($data['consignment_id'], 'consignment_number');
                        if (isset($decodeResponse['request_id']) && ( is_null($consignmentModel->getLabelRequestId()) )) {
                            $consignmentModel->setIsLabelCreated(1)
                            ->setIsLabelPrinted(1)
                            ->setModifyDate($date)
                            ->setLabelRequestId($decodeResponse['request_id'])
                            ->save();
                            $orderId = $consignmentModel->getOrderId();
                            $orderToPrepare = $this->order->load($orderId);
                            $orderToPrepare->setIsLabelGenerated(1)
                                        ->setIsLabelPrinted(1)
                                        ->save();
                        } else {
                            $consignmentModel->setIsLabelCreated(1)
                                        ->setIsLabelPrinted(1)
                                        ->setModifyDate($date)
                                        ->save();
                            $orderId = $consignmentModel->getOrderId();
                            $orderToPrepare = $this->order->load($orderId);
                            $orderToPrepare->setIsLabelGenerated(1)
                                        ->setIsLabelPrinted(1)
                                        ->save();
                        }
                    }
                    
                    $fileSystem = $this->_objectManager->create('\Magento\Framework\Filesystem');
                    $filePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                    $dir = $filePath . 'biztech/eParcelPdf';
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }

                    /*label generated. download label by clicking URL*/
                    $label_URL = array();
                    foreach ($finalArray as $key => $value) {
                        $label_URL['_query']['labels']['label_'.$key] = $value;
                    }
                    $url = $this->getUrl('biztech_ausposteparcel/consignment/downloadLabels',$label_URL);
                    $noticeMsg = __(
                        'Label Generated, Download From <a href="%1" target="_blank">Here</a>',$url);
                    $this->messageManager->addNotice($noticeMsg);
                    return $this->_redirect('*/*/index');

                    /*download label in same controller*/
                    $labelsContent = [];
                    foreach ($finalArray as $i => $j) {
                        $labelPath = $j;
                        $labelContent =  file_get_contents($labelPath);
                        if ($labelContent) {
                            $labelsContent[] = $labelContent;
                        }
                    }

                    if (!empty($labelsContent)) {
                        $outputPdf = $this->labelGenerator->combineLabelsPdf($labelsContent);
                        return $this->fileFactory->create(
                            'ShippingLabels.pdf',
                            $outputPdf->render(),
                            DirectoryList::VAR_DIR,
                            'application/pdf'
                        );
                    }
                }
            }
            $this->_redirect('*/*/index');
        }
    }
}
