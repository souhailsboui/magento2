<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @property messageManager
 */
class ReturnLabelCreate extends Action
{
    public $apimodel;
    public $consignmentmodel;
    protected $responseinterface;
    protected $jsonHelper;

    public function __construct(
        Context $context,
        \Biztech\Ausposteparcel\Model\Api $apimodel,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Magento\Framework\Json\Helper\Data $data,
        \Magento\Framework\App\ResponseInterface $responseinterface
    ) {
        $this->jsonHelper = $data;
        $this->responseinterface = $responseinterface;
        $this->apimodel = $apimodel;
        $this->messageManager = $context->getMessageManager();
        $this->consignmentmodel = $consignmentmodel;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $consignmentNumber = $data['consignment_number'];
        $orderId = $data['order_id'];
        $eparcelShippingId = $data['eparcelShippingId'];
        $consignment = $this->consignmentmodel->load($orderId, 'order_id');

        try {
            $labelContent = $this->apimodel->sendConsignment($consignmentNumber, $orderId, $returnlabels = 1);
            $getResponseDecode = $this->jsonHelper->jsonDecode($labelContent);
            $getResponseDecode1 = $this->jsonHelper->jsonDecode($labelContent, true);
            $status = "";
            if (isset($getResponseDecode1['status'])) {
                if ($getResponseDecode1['status'] == 'error') {
                    $message = $getResponseDecode1['message'][0]['message'];
                    $this->messageManager->addError(__($message));
                    $this->_redirect("sales/order/view", array('order_id' => $orderId, 'active_tab' => 'auspost_eparcel'));
                    return;
                }
            }

            if (isset($getResponseDecode1['labels']['lable_type']) && ($getResponseDecode1['labels']['lable_type'] == 1)) {
                $timestamp = time();
                $date = date('Y-m-d H:i:s', $timestamp);
                if ($getResponseDecode1[0]->code) {
                    $message = $getResponseDecode[0]->message;
                    $this->messageManager->addError(ucfirst($message));
                } else {
                    $status = $getResponseDecode1['status'];
                    if ($status === 'error') {
                        $message = rtrim($getResponseDecode1['message'][0]['message'], '.');
                        ;
                        $message = __($message . ' ' . 'for order #' . $increamentId);
                        $this->messageManager->addError($message);
                        $this->_redirect("biztech_ausposteparcel/consignment/create", array('order_id' => $orderId, 'consignment_number' => $consignmentNumber));
                    } else {
                        foreach ($getResponseDecode1 as $key => $value) {
                            if ($key == 'labels') {
                                $labelRequestObj = $value;
                                $request_id = $labelRequestObj[0]['shipment_ids'][0];
                                $consignment->setPrintReturnLabels($request_id)->save();
                                $eparcel_consignment_id = $consignment['eparcel_consignment_id'];
                                $getResponse = $this->apimodel->downloadLabel($consignmentNumber, $eparcel_consignment_id, $request_id, $returnlabel = 1);
                                $decodeData = $this->jsonHelper->jsonDecode($getResponse);
                                $message = $decodeData->message;

                                if (isset($message) && $message != 'URL Exist') {
                                    $this->messageManager->addError($message);
                                    $this->_redirect("sales/order/view", array('order_id' => $orderId, 'active_tab' => 'auspost_eparcel'));
                                } else {
                                    if ($decodeData->status == 'success') {
                                        $filename = "returnLabel" . $eparcel_consignment_id . ".pdf";
                                        $pdfurl = $decodeData->pdf_url;
                                        $content = file_get_contents($pdfurl);
                                        return $this->_sendUploadResponse($filename, $content);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                if ($status === 'error') {
                    $message = rtrim($getResponseDecode1['message'][0]['message'], '.');
                    $message = __($message . ' ' . 'for order #' . $increamentId);
                    $this->messageManager->addError($message);
                    $this->_redirect("biztech_ausposteparcel/consignment/create", array('order_id' => $orderId, 'consignment_number' => $consignmentNumber));
                } else {
                    foreach ($getResponseDecode1 as $key => $value) {
                        if ($key == 'labels') {
                            $labelRequestObj = $value;
                            $request_id = $labelRequestObj[0]['shipment_ids'][0];
                            $consignment->setPrintReturnLabels($request_id)->save();
                            $eparcel_consignment_id = $consignment['eparcel_consignment_id'];
                            $getResponse = $this->apimodel->downloadLabel($consignmentNumber, $eparcel_consignment_id, $request_id, $returnlabel = 1);
                            $decodeData = $this->jsonHelper->jsonDecode($getResponse);
                            if (isset($decodeData->message)) {
                                $message = $decodeData->message;
                            } else {
                                $message = "";
                            }
                            
                            //if (isset($message) && $message != 'URL Exist') {
                            if ($message != '') {
                                $this->messageManager->addError($message);
                                $this->_redirect("sales/order/view", array('order_id' => $orderId, 'active_tab' => 'auspost_eparcel'));
                            } else {
                                if ($decodeData['status'] == 'success') {
                                    $filename = "returnLabel" . $eparcel_consignment_id . ".pdf";
                                    $pdfurl = $decodeData['pdf_url'];
                                    $content = file_get_contents($pdfurl);
                                    return $this->_sendUploadResponse($filename, $content);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Cannot create consignment return label.') . $e->getMessage());
            $this->_redirect("sales/order/view", array('order_id' => $orderId, 'active_tab' => 'auspost_eparcel'));
        }
    }

    public function _sendUploadResponse($fileName, $content, $contentType = 'application/pdf')
    {
        $response = $this->responseinterface;
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
