<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @property  messageManager
 */
class Submitconsignment extends Action
{
    public $order;
    public $apimodel;
    public $articlemodel;
    public $trackinghelper;
    public $consignmentmodel;
    public $Date;
    protected $jsonHelper;

    public function __construct(
        Context $context,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Model\Api $apimodel,
        \Biztech\Ausposteparcel\Model\Article $articlemodel,
        \Biztech\Ausposteparcel\Helper\Tracking $trackinghelper,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->apimodel = $apimodel;
        $this->messageManager = $context->getMessageManager();
        $this->articlemodel = $articlemodel;
        $this->trackinghelper = $trackinghelper;
        $this->consignmentmodel = $consignmentmodel;
        $this->jsonHelper = $jsonHelper;
        $this->Date = $date;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $consignmentId = $this->getRequest()->getParam('consignment_number');
        $orderId = $this->getRequest()->getParam('order_id');

        $OrderToPrepare = $this->order->load($orderId);
        $increamentId = $OrderToPrepare->getData('increment_id');
        $shipment = $OrderToPrepare->getShipmentsCollection()->getFirstItem();
        $shipmetData = $shipment->getData();
        
        $shippingMethod = $OrderToPrepare->getShippingMethod();

        $getResponse = $this->apimodel->sendConsignment($consignmentId, $orderId);
        $consignment = $this->consignmentmodel->load($orderId, 'order_id');
        
        $getResponseDecode = $this->jsonHelper->jsonDecode($getResponse);

        $getResponseDecode1 = $this->jsonHelper->jsonDecode($getResponse, true);
        
        $date = date("Y-m-d H:i:s", $this->Date->timestamp());
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
                $message = (__($message . ' ' . 'for order #' . $increamentId));
                $this->messageManager->addError($message);
                $this->_redirect("sales/order/view", array('order_id' => $orderId, 'consignment_number' => $consignmentId));
                return;
            } else {
                if (is_array($getResponseDecode1) || is_object($getResponseDecode1)) {
                    foreach ($getResponseDecode1 as $key => $value) {
                        if ($key == 'shipments') {
                            $shipmentobj = $value[0];
                            if (is_array($shipmentobj)) {
                                $shipment_id = $shipmentobj['shipment_id'];
                                $shipment_reference = $shipmentobj['shipment_reference'];
                                $shipment_creation_date = $shipmentobj['shipment_creation_date'];

                                foreach ($shipmentobj['items'] as $i => $j) {
                                    /* load consignment by consignment id  and insert eparcel_consignment_id */

                                    $eparcelConsignmentId = $j['tracking_details']['consignment_id'];
                                    $consignment->setEparcelConsignmentId($eparcelConsignmentId)->save();

                                    /* load aricle data abd save articles */

                                    $eparcel_article_id = $j['tracking_details']['article_id'];
                                    if (isset($j['item_reference'])) {
                                        $article_ref = $j['item_reference'];
                                        $article = $this->articlemodel->getCollection()
                                            ->addFieldToFilter('article_number', $article_ref)
                                            ->addFieldToFilter('order_id', $orderId)->getLastItem();
                                        $article->setEparcelArticleId($eparcel_article_id)->save();
                                    }

                                    //add tracking details
                                    $trackingLabel = 'Auspost eparcel';
                                    $shipment_reference_tracking = $shipmetData['increment_id'];
                                    $result = $this->trackinghelper->addTrackingToShipment($j['tracking_details']['consignment_id'], $shipment_reference_tracking, $shippingMethod, $trackingLabel);
                                }
                            }
                            if (is_object($shipmentobj)) {
                                $shipment_id = $shipmentobj->shipment_id;
                                $shipment_reference = $shipmentobj->shipment_reference;
                                $shipment_creation_date = $shipmentobj->shipment_creation_date;

                                foreach ($shipmentobj->items as $i => $j) {
                                    // load consignment by consignment id  and insert eparcel_consignment_id
                                    $eparcelConsignmentId = $j->tracking_details->consignment_id;
                                    $consignment->setEparcelConsignmentId($eparcelConsignmentId)->save();

                                    /* load aricle data abd save articles */

                                    $eparcel_article_id = $j->tracking_details->article_id;
                                    if (isset($j->item_reference)) {
                                        $article_ref = $j->item_reference;
                                        $article = $this->articlemodel->getCollection()
                                            ->addFieldToFilter('article_number', $article_ref)
                                            ->addFieldToFilter('order_id', $orderId)->getLastItem();
                                        $article->setEparcelArticleId($eparcel_article_id)->save();
                                    }

                                    //add tracking details
                                    $trackingLabel = 'Auspost eparcel';

                                    $shipment_reference_tracking = $shipmetData['increment_id'];
                                    $result = $this->trackinghelper->addTrackingToShipment($j->tracking_details->consignment_id, $shipment_reference_tracking, $shippingMethod, $trackingLabel);
                                }
                            }
                        } elseif ($key == 'labels') {
                            $error = '';
                            if (isset($value['status'])) {
                                $error = $value['status'];
                            }
                            if ($error == 'error') {
                                $message = rtrim($value['message'][0]['message'], '.');
                                $message = (__($message . ' ' . 'for order #' . $increamentId));
                                $this->messageManager->addError($message);
                                $this->_redirect("sales/order/view", array('order_id' => $orderId, 'consignment_number' => $consignmentId));
                                return;
                            } else {
                                $request_id = $value[0]['request_id'];
                                if (isset($value[0]['shipment_ids'][0])) {
                                    $shipmentId = $value[0]['shipment_ids'][0];
                                } else {
                                    $shipmentId = $value[0]['shipment_ids'];
                                }
                                /* save request id */
                                $consignment->setLabelRequestId($request_id)->save();
                                $consignment->setModifyDate($date)->save();

                                /* Save eparcel shipment id in order table */
                                $OrderToPrepare->setEparcelShippingId($shipmentId)->save();
                                $OrderToPrepare->setIsLabelGenerated(1)->save();

                                /* save generated lable and then create and dispatch order */
                                
                                $consignmentModel = $this->consignmentmodel->load($orderId, 'order_id');
                                $consignmentlabel = $consignmentModel->getData();
                                $consignment_number = $consignmentlabel['consignment_number'];
                                $eparcel_consignment_id = $consignmentlabel['eparcel_consignment_id'];
                                $label_request_id = $consignmentlabel['label_request_id'];
                                
                                $getResponse = $this->apimodel->downloadLabel($consignment_number, $eparcel_consignment_id, $label_request_id);
                                $decodeData1 = $this->jsonHelper->jsonDecode($getResponse);

                                if ($decodeData1['status'] == 'success') {
                                    // save locally if user needed.
                                    $timestamp = time();
                                    $date = date('Y-m-d H:i:s', $timestamp);
                                    $consignmentModel = $this->consignmentmodel->load($consignment_number, 'consignment_number')
                                    ->setIsLabelCreated(1)
                                    ->setIsLabelPrinted(1)
                                    ->setModifyDate($date)
                                    ->save();
                                } else {
                                    $message = $decodeData1['message'];
                                    //        if (isset($message) || $message != "") {}
                                    $this->messageManager->addError($message);
                                    $this->_redirect("sales/order/view", array('order_id' => $orderId, 'active_tab' => 'auspost_eparcel'));
                                }
                            }
                        }
                    }
                }
                $consignment->setModifyDate($date)->save();
                $this->_redirect("sales/order/view", array('order_id' => $orderId, 'consignment_number' => $consignmentId));
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
}
