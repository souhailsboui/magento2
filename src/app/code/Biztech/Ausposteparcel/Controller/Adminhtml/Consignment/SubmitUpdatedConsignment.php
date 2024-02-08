<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @property  messageManager
 */
class SubmitUpdatedConsignment extends Action
{
    public $order;
    public $apimodel;
    public $articlemodel;
    public $trackinghelper;
    public $consignmentmodel;
    public $Date;

    public function __construct(
        Context $context,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Model\Api $apimodel,
        \Biztech\Ausposteparcel\Model\Article $articlemodel,
        \Biztech\Ausposteparcel\Helper\Tracking $trackinghelper,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->apimodel = $apimodel;
        $this->messageManager = $context->getMessageManager();
        $this->articlemodel = $articlemodel;
        $this->trackinghelper = $trackinghelper;
        $this->consignmentmodel = $consignmentmodel;
        $this->Date = $date;
    }

    public function execute()
    {
        $consignmentId = $this->getRequest()->getParam('consignment_number');
        $orderId = $this->getRequest()->getParam('order_id');

        $OrderToPrepare = $this->order->load($orderId);
        $increamentId = $OrderToPrepare->getData('increment_id');
        $shipment = $OrderToPrepare->getShipmentsCollection()->getFirstItem();
        $shipmetData = $shipment->getData();

        $shippingMethod = $OrderToPrepare->getShippingMethod();
        $getResponse = $this->apimodel->sendUpdatedConsignment($consignmentId, $orderId);
        $consignment = $this->consignmentmodel->load($orderId, 'order_id');
        $this->jsonHelper = $this->_objectManager->get('Magento\Framework\Json\Helper\Data');
        $getResponseDecode = $this->jsonHelper->jsonDecode($getResponse);
        $date = date("Y-m-d H:i:s", $this->Date->timestamp());
        if (isset($getResponseDecode[0]->code)) {
            $message = $getResponseDecode[0]->message;
            $this->messageManager->addError($message);
        } else {
            $status = '';
            if (isset($getResponseDecode['status'])) {
                $status = $getResponseDecode['status'];
            }
            if ($status === 'error') {
                $message = rtrim($getResponseDecode['message'][0]['message'], '.');
                $message = (__($message . ' ' . 'for order #' . $increamentId));
                $this->messageManager->addError($message);
                $this->_redirect("biztech_ausposteparcel/consignment/create", array('order_id' => $orderId, 'consignment_number' => $consignmentId));
                return;
            } else {
                $getLabelResponse = $this->apimodel->getUpdatedLabel($consignmentId, $orderId);
                $getLabelResponseDecode = $this->jsonHelper->jsonDecode($getLabelResponse, true);

                foreach ($getLabelResponseDecode as $key => $value) {
                    if ($key == 'labels') {
                        /* Save REquest id in database for generate label        */
                        $labelRequestObj = $value[0];
                        $request_id = $labelRequestObj['request_id'];

                        /* save request id */
                        $consignment->setLabelRequestId($request_id)->save();
                        $consignment->setModifyDate($date)->save();

                        /* Save eparcel shipment id in order table */
                        $shipmentIds = $labelRequestObj['shipment_ids'];
                        $OrderToPrepare->setEparcelShippingId($shipmentIds[0])->save();
                        $OrderToPrepare->setIsLabelGenerated(1)->save();

                        $getConsignmentDetails = $this->apimodel->getConsignmentDetails($shipmentIds[0]);
                        $getConsignmentDetails = $this->updateArticleConsignmenrt($getConsignmentDetails, $increamentId, $consignmentId, $orderId, $OrderToPrepare);
                        $consignment_number = $consignment->getConsignmentNumber();
                        $eparcel_consignment_id = $consignment->getEparcelConsignmentId();
                        $label_request_id = $consignment->getLabelRequestId();
                        $getUpdatedLabel = $this->apimodel->downloadUpdatedLabel($consignment_number, $eparcel_consignment_id, $label_request_id);

                        //Start: Dispatch consignment immediate after labe generate
                        $orderData = array();
//                        $orderData['shipments'][]['shipment_id'] = $shipmentId;
                        $orderData['shipments'][]['shipment_id'] = $shipmentIds[0];
                        $getResponse = $this->apimodel->orderSummary($orderData);
                        if ($getResponse['status'] == 'success') {
                            $url = $getResponse['url'];
                            $date = date("Y-m-d H:i:s", $this->Date->timestamp());
                            if (isset($url)) {
                                $manifest_collection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest')->getCollection()->getLastItem();
                                //$manifest_collection = $this->ausposteParcelManifestFactory->create()->getLastItem();
                                $oldManifestNumber = $manifest_collection->getManifestNumber();

                                if ($oldManifestNumber) {
                                    $oldManifestId = str_replace('M', '', $oldManifestNumber);
                                    $newmanifestInc = (intval($oldManifestId) + 1);
                                    $manifestId = str_pad($newmanifestInc, 9, "0", STR_PAD_LEFT);
                                    $manifestNumber = 'M' . $manifestId;
                                } else {
                                    $manifestNumber = 'M000000001';
                                }
                                $manifestNumber = trim($manifestNumber);

                                $fileSystem = $this->_objectManager->create('\Magento\Framework\Filesystem');
                                $filePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                                $dir = $filePath . 'biztech/orderSummary';
                                if (!file_exists($dir)) {
                                    mkdir($dir, 0777, true);
                                }
                                $dirPath = $dir . "/" . $manifestNumber . ".pdf";
                                $pdfContent = $this->get_web_page($url);
                                file_put_contents($dirPath, $pdfContent);
                                $filename = 'order-summary-' . $manifestNumber . ".pdf";
                                $labelurl = 'orderSummary' . "/" . $manifestNumber . ".pdf";

                                if (strtolower($manifestNumber) != 'unassinged') {
                                    $manifest = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest');
                                    $articleCollection = $this->articlemodel->getCollection()->addFieldToFilter('consignment_number', $consignmentId);
                                    if (isset($articleCollection)) {
                                        $numberOfArticles = count($articleCollection);
                                    } else {
                                        $numberOfArticles = 0;
                                    }
                                    $insertData = array(
                                        'manifest_number' => $manifestNumber,
                                        'number_of_articles' => $numberOfArticles,
                                        'number_of_consignments' => 1,
                                        'despatch_date' => $date,
                                        'label' => $labelurl
                                    );

                                    $manifest->setData($insertData);
                                    try {
                                        $manifest->save()->getManifestId();
                                    } catch (\Exception $e) {
                                        $error = __('Cannot create Manifest, Error: ') . $e->getMessage();
                                        $this->messageManager->addError($error);
                                    }
                                }

                                $content = file_get_contents($dirPath);
                                $consignment->setModifyDate($date)
                                        ->setManifestNumber($manifestNumber)
                                        ->setIsNextManifest(0)
                                        ->save();
                                $message = (__('Label has been generated and dispatched successfully for order #' . $increamentId));
                                $this->messageManager->addSuccess($message);
                                $this->_sendUploadResponse($filename, $content, 'application/pdf');
                                //TODO reload page after pdf generation
                                $this->_redirect("sales/order/view", array('order_id' => $orderId, 'consignment_number' => $consignmentId));
                            } else {
                                $message = 'something went wrong';
                                $this->messageManager->addError($message);
                                $this->_redirect("sales/order/view", array('order_id' => $orderId, 'consignment_number' => $consignmentId));
                            }
                        } elseif ($getResponse['status'] == 'error') {
                            $message = $getResponse['message'][0]['message'];
                            $this->messageManager->addError($message);
                            $this->_redirect("sales/order/view", array('order_id' => $orderId, 'consignment_number' => $consignmentId));
                        }
                        //End: Dispatch consignment immediate after labe generate
                    }
                }
                $consignment->setModifyDate($date)->save();
            }
        }
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

    public function _sendUploadResponse($fileName, $content, $contentType = 'application/pdf')
    {
        $response = $this->_objectManager->get('\Magento\Framework\App\ResponseInterface');
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

    public function updateArticleConsignmenrt($getResponse, $increamentId, $consignmentId, $orderId, $OrderToPrepare)
    {
        $shippingMethod = $OrderToPrepare->getShippingMethod();
        $shipment = $OrderToPrepare->getShipmentsCollection()->getFirstItem();
        $shipmetData = $shipment->getData();
        $shipment_reference_tracking = $shipmetData['increment_id'];

        $getResponseDecode = json_decode($getResponse);
        $getResponseDecode1 = json_decode($getResponse, true);
//        $status = $getResponseDecode1['status'];
        if (isset($getResponseDecode1[0]->code)) {
            $message = $getResponseDecode[0]->message;
            $this->messageManager->addError($message);
        } else {
            $status = '';
            if (isset($getResponseDecode1['status'])) {
                $status = $getResponseDecode1['status'];
            }
            if ($status === 'error') {
                $message = rtrim($getResponseDecode1['message'][0]['message'], '.');
                $message = __($message . ' ' . 'for order #' . $increamentId);
                $this->messageManager->addError($message);
                $this->_redirect("adminhtml/consignment/create", array('order_id' => $orderId, 'consignment_number' => $consignmentId));
            } else {
                $consignment = $this->consignmentmodel->load($orderId, 'order_id');
                foreach ($getResponseDecode as $key => $value) {
                    if ($key == 'shipments') {
                        $shipmentobj = $value[0];
                        $shipment_id = $shipmentobj->shipment_id;
                        $shipment_reference = $shipmentobj->shipment_reference;
                        $shipment_creation_date = $shipmentobj->shipment_creation_date;
                        //inser Tracking Details
                        foreach ($shipmentobj->items as $i => $j) {
                            /* load consignment by consignment id  and insert eparcel_consignment_id */

                            $eparcelConsignmentId = $j->tracking_details->consignment_id;
                            $consignment->setEparcelConsignmentId($eparcelConsignmentId)
                                    ->save();

                            /* load aricle data abd save articles */
                            $eparcel_article_id = $j->tracking_details->article_id;
                            $article_ref = $j->item_reference;
                            $article_item_id = $j->item_id;
                            $article = $this->articlemodel->load($article_ref, 'article_number');
                            $article->setEparcelArticleId($eparcel_article_id)
                                    ->setItemId($article_item_id)
                                    ->save();

                            //add tracking details
                            $trackingLabel = 'Auspost eparcel';
                            $result = $this->trackinghelper->addTrackingToShipment($j->tracking_details->consignment_id, $shipment_reference_tracking, $shippingMethod, $trackingLabel);
                        }
                    }
                }
                $date = date("Y-m-d H:i:s", $this->Date->timestamp());
                $consignment->setModifyDate($date)->save();
            }
        }
    }
}
