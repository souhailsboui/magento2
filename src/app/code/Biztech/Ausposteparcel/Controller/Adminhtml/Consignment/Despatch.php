<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;

class Despatch extends Action
{
    public $consignmentCollectionFactory;
    public $order;
    public $apimodel;
    public $Date;
    public $consignmentmodel;
    protected $storeManager;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var Registry
     */
    private $registry;

    protected $shipmentNotifier;
    
    protected $_shipmentCollection;

    public function __construct(
        Context $context,
        \Biztech\Ausposteparcel\Model\Cresource\Consignment\CollectionFactory $consignmentCollectionFactory,
        \Biztech\Ausposteparcel\Model\Api $apimodel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        PageFactory $resultPageFactory,
        Registry $registry,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoaderFactory $shipmentLoader,
        \Magento\Shipping\Model\ShipmentNotifierFactory $shipmentNotifier,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollection
    ) {
        $this->consignmentCollectionFactory = $consignmentCollectionFactory;
        $this->messageManager = $context->getMessageManager();
        $this->order = $order;
        $this->apimodel = $apimodel;
        $this->storeManager = $storeManager;
        $this->Date = $date;
        $this->registry = $registry;
        $this->shipmentLoader = $shipmentLoader;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->consignmentmodel = $consignmentmodel;
        $this->_shipmentCollection = $shipmentCollection;
        parent::__construct($context);
    }

    public function execute()
    {
        $consignmentModel = $this->consignmentCollectionFactory->create()->addFieldToFilter('is_next_manifest', 1);
        $consignmentId = array();

        foreach ($consignmentModel->getData() as $key => $value) {
            $orderToPrepare = $this->order->load($value['order_id']);
            $eparcelShippingId = $orderToPrepare->getEparcelShippingId();
            $is_submit_to_eparcel = $orderToPrepare->getIsSubmittedToEparcel();
            if (isset($eparcelShippingId) && ($is_submit_to_eparcel == null || $is_submit_to_eparcel == 0)) {
                $shippedOrders[$value['order_id']] = $eparcelShippingId;
                /*start - get error of exact match from auspost - 2-dec-2020 by JH*/
                $manifestedOrder[$eparcelShippingId] =  $orderToPrepare->getIncrementId();
                /*end*/
                $orderId[] = $value['order_id'];
            } else {
                continue;
            }
        }

        $orderData = array();
        $index = 0;
        $message = __('No orders to submit');
        if (empty($shippedOrders)) {
            $this->messageManager->addError($message);
            return $this->_redirect('*/*/index');
        }

        foreach ($shippedOrders as $key => $value) {
            $orderData['shipments'][]['shipment_id'] = $value;
            $index++;
        }

        if (empty($orderData)) {
            $this->messageManager->addNotice("No order to submit");
            return $this->_redirect('*/*/index');
        }
        $getResponse = $this->apimodel->orderSummary($orderData);
        if ($getResponse['status'] == 'success') {
            $url = $getResponse['url'];
            $date = date("Y-m-d H:i:s", $this->Date->timestamp());
            if (isset($url)) {
                $manifest = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest');
                $manifest_collection = $manifest->getCollection()->addFieldToFilter('despatch_date', null)->getLastItem();
                $oldManifestNumber = $manifest_collection->getManifestNumber();
                $oldManifestId = $manifest_collection->getManifestId();


                $setManifestData = $manifest->load($oldManifestId);
                $setManifestData->SetData('despatch_date', $date);

                $timestamp = $this->Date->timestamp(strtotime($date));
                $fileSystem = $this->_objectManager->create('\Magento\Framework\Filesystem');
                $filePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                $dir = $filePath . 'biztech/orderSummary';

                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $dirPath = $dir . "/" . $oldManifestNumber . ".pdf";
                $pdfContent = $this->get_web_page($url);
                file_put_contents($dirPath, $pdfContent);
                $filename = 'order-summary-' . $oldManifestNumber . ".pdf";
                $labelurl = 'orderSummary' . "/" . $oldManifestNumber . ".pdf";
                $setManifestData->SetData('label', $labelurl)
                    ->save();

                foreach ($orderId as $id) {
                    $consignment = $this->consignmentmodel->load($id, 'order_id');
                    $consignment->setModifyDate($date)
                        ->setIsNextManifest(0)
                        ->setDespatched(1)
                        ->save();
                }
                foreach ($orderId as $id) {
                    $consignment = $this->consignmentmodel->load($id, 'order_id');
                    if ($consignment->getNotifyCustomers()) {
                        $orderToPrepare = $this->order->load($id);
                        if (!$orderToPrepare->canShip()) {
                            // $shipment = $orderToPrepare->getShipmentsCollection()->getLastItem();
                            $shipment = $this->_shipmentCollection->create()->addFieldToFilter('order_id',$id)->getLastItem();
                            $shipment = $this->shipmentLoader->create()->setShipmentId($shipment->getId())->load();
                            if ($shipment) {
                                $this->shipmentNotifier->create()->notify($shipment);
                                $shipment->save();
                                if ($this->registry->registry('current_shipment')) {
                                    $this->registry->unregister('current_shipment');
                                }
                            }
                        }
                    }
                }

                $labelLink = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'biztech/' . $labelurl;

                $noticeMsg = __(
                    'Download Submitted <a href="%1" target="_blank">Manifest\'s</a> Order Summary from <a href="%2" target="_blank"> Here</a>',
                    $this->getUrl('biztech_ausposteparcel/manifest/index'),
                    $labelLink
                );
                $this->messageManager->addNotice($noticeMsg);
                $this->_redirect('*/*/index');
                /* $content = file_get_contents($dirPath);
                return $this->_sendUploadResponse($filename, $content); */
            } else {
                $message = 'something went wrong';
                $this->messageManager->addError($message);
                $this->_redirect('*/*/index');
            }
        } elseif ($getResponse['status'] == 'error') {
            /*start - get error of exact match from auspost - 2-dec-2020 by JH*/
            $errorOrder = "";
            foreach ($getResponse['message'] as $key => $value) {
                $errorOrder.= $manifestedOrder[$value['context']['shipment_id']].", ";
            }
            /*end*/
            $message = $getResponse['message'][0]['message'];
            $this->messageManager->addError("<b>".$message."</b> Same error getting with <b>".count($getResponse['message'])." orders:</b> ".rtrim($errorOrder,", ")." So you need to remove these orders from under the manifest and then dispatch an order.");
            $this->_redirect('*/*/index');
        } elseif ($getResponse['status'] == 'errorDisable') {
            $message = $getResponse['message'];
            $this->messageManager->addError($message);
            $this->_redirect('*/*/index');
        }
    }

    public function _sendUploadResponse($fileName, $content, $contentType = 'application/pdf')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $response = $objectManager->get('\Magento\Framework\App\ResponseInterface');
        $response->setHttpResponseCode(200);
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        return;
    }

    public function get_web_page($url)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_USERAGENT => "spider", // who am i
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }
}