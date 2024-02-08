<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class MassShipmentGenerate extends Action
{
    public $order;
    public $consignmentmodel;
    public $apimodel;
    public $articlemodel;
    protected $_trackFactory;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;
    protected $messageManager;
    protected $jsonHelper;

    /**
     * @var Registry
     */
    private $registry;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        Registry $registry,
        \Biztech\Ausposteparcel\Model\Api $apimodel,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Biztech\Ausposteparcel\Model\Article $articlemodel,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->order = $order;
        $this->consignmentmodel = $consignmentmodel;
        $this->apimodel = $apimodel;
        $this->registry = $registry;
        $this->shipmentLoader = $shipmentLoader;
        $this->_trackFactory = $trackFactory;
        $this->articlemodel = $articlemodel;
    }

    public function execute()
    {
        $getParams = $this->getRequest()->getParams();
        $ids = $getParams['order_consignment'];

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        if (!isset($ids)) {
            $this->messageManager->addError(__('Please select item(s)'));
            $this->_redirect('*/*/index');
            return;
        } else {
            foreach ($ids as $key => $value) {
                $order_ids[] = explode('_', $value);
            }
            $ignoreArray = array();
            foreach ($order_ids as $key1 => $value1) {
                $orderId = $value1[0];
                $consignment = $this->consignmentmodel->load($orderId, 'order_id')->getData();
                if (sizeof($consignment)) {
                    $increamentIds = $this->order->load($orderId)->getIncrementId();
                    if ($consignment['is_label_printed'] == 1 && $consignment['is_label_created'] == 1) {
                        $ignoreArray[] = $value1[1];
                    } else {
                        $consignmentIds[] = $value1;
                        $cIds[] = $value1[1];
                    }
                    if ($value1[1] == 0) {
                        $notGeneratedConsignments[] = $increamentIds;
                    }
                } else {
                    $increamentIds = $this->order->load($orderId)->getIncrementId();
                    $notGeneratedConsignments[] = $increamentIds;
                }
            }
            if (!empty($ignoreArray)) {
                $ignoreConsignmentIds = implode(",", $ignoreArray);
                $message = __('Label already generated for consignments') . " " . $ignoreConsignmentIds;
                $this->messageManager->addError($message);
                $this->_redirect('*/*/index');
                return;
            }
            if (!empty($notGeneratedConsignments)) {
                $ignoreConsignmentIds = implode(",", $notGeneratedConsignments);
                $message = __('Please generate consignments first for order(s)') . " " . $ignoreConsignmentIds;
                $this->messageManager->addError($message);
                $this->_redirect('*/*/index');
                return;
            } else {
                $getResponse = $this->apimodel->massShipmentGenerate($consignmentIds);
                $this->jsonHelper = $this->_objectManager->get('Magento\Framework\Json\Helper\Data');
                $decodeResponse = $this->jsonHelper->jsonDecode($getResponse, true);

                if (isset($decodeResponse['status']) == 'error') {
                    if ($decodeResponse['status'] == 'error') {
                        $responseMessage = $decodeResponse['message'][0]['message'];
                        $this->messageManager->addError($responseMessage);
                        $this->_redirect('*/*/index');
                        return;
                    }
                }

                if (!empty($ignoreArray)) {
                    $ignoreConsignmentIds = implode(",", $ignoreArray);
                    $message = __('Label already generated for consignments') . " " . $ignoreConsignmentIds;
                    $this->messageManager->addError($message);
                }
                if ($decodeResponse['shipments']) {
                    foreach ($decodeResponse['shipments'] as $key => $value) {
                        $eParcelShipmentId = $value['shipment_id'];
                        $ShipmentGeneratedconsignmentId[] = $value['shipment_reference'];
                        if (isset($value['items'])) {
                            foreach ($value['items'] as $itemKey => $itemValue) {
                                if (isset($itemValue['item_reference'])) {
                                    $tracking_details[$value['shipment_reference']]['article_id'][] = $itemValue['item_reference'];
                                }
                                $tracking_details[$value['shipment_reference']]['eparce_shipment_id'] = $value['shipment_id'];

                                $tracking_details[$value['shipment_reference']]['eparcel_article_id'][] = $itemValue['tracking_details']['article_id'];
                                $tracking_details[$value['shipment_reference']]['eparcel_consignment_id'] = $itemValue['tracking_details']['consignment_id'];
                            }
                        }
                    }

                    //save Data
                    $this->saveTrackingDetails($tracking_details);

                    foreach ($consignmentIds as $key => $value) {
                        $successConsignmentIds[] = $value[1];
                    }
                    $cIds = implode(',', $successConsignmentIds);
                    $message = 'Shipment has been generated successfully for consignment(s) number ' . $cIds;
                    $this->messageManager->addSuccess($message);
                    $this->_redirect('*/*/index');
                    return;
                }
            }
        }
    }

    public function saveTrackingDetails($trackingDetails)
    {
        foreach ($trackingDetails as $key => $value) {
            $timestamp = time();
            $date = date('Y-m-d H:i:s', $timestamp);

            //save consignment data in consignment table
            $consignmentModel = $this->consignmentmodel->load($key, 'consignment_number')
                ->setEparcelConsignmentId($value['eparcel_consignment_id'])
                ->setModifyDate($date);
            $consignmentModel->save();
            $orderId = $consignmentModel->getOrderId();
           
            //Save Article Data in Article table
            if (isset($value['article_id'])) {
                if (is_array($value['article_id'])) {
                    foreach ($value['article_id'] as $key => $valueofarticle) {
                        $article = $this->articlemodel->getCollection()->addFieldToFilter('order_id', $orderId)->addFieldToFilter('article_number', $valueofarticle);
                        $articleData = $this->articlemodel->load($article->getLastItem()->getArticleId(), 'article_id');
                        $articleData->setEparcelArticleId($value['eparcel_article_id'][$key])->save();
                    }
                }
            }

            /*Save auspost tracking & shipment detail*/
            $this->order->load($orderId);
            $shippingMethod = $this->order->getShippingMethod();
            $shipmentCollection = $this->_objectManager->create('\Magento\Sales\Api\OrderRepositoryInterface');
            $orderRepository = $shipmentCollection->get($orderId);
            $shipmenttotal = $orderRepository->getShipmentsCollection();
            foreach ($shipmenttotal as $shipment) {
                $shipmentId = $shipment->getId();
            }

            $carrier = 'custom';
            $number = $value['eparcel_consignment_id'];
            $title = 'Auspost eparcel';

            $this->shipmentLoader->setOrderId($orderId);
            $this->shipmentLoader->setShipmentId($shipmentId);
            $this->shipmentLoader->setShipment(null);
            $this->shipmentLoader->setTracking(null);
            $shipment = $this->shipmentLoader->load();
            if ($shipment) {
                $track = $this->_objectManager->create(
                    \Magento\Sales\Model\Order\Shipment\Track::class
                )->setNumber(
                    $number
                )->setCarrierCode(
                    $carrier
                )->setTitle(
                    $title
                );
                $shipment->addTrack($track)->save();
            }

            if ($this->registry->registry('current_shipment')) {
                $this->registry->unregister('current_shipment');
            }

            //Save eParcel Shipment Id into sales order table
            $this->order->setEparcelShippingId($value['eparce_shipment_id'])->save();
            $this->order->setIsLabelGenerated(0)->save();
        }
    }
}
