<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @property  messageManager
 */
class LabelCreate extends Action
{
    public $order;
    public $apimodel;
    public $articlemodel;
    public $trackinghelper;
    public $consignmentmodel;
    public $Date;
    protected $jsonHelper;
    protected $responseinterface;

    public function __construct(
        Context $context,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Model\Api $apimodel,
        \Biztech\Ausposteparcel\Model\Article $articlemodel,
        \Biztech\Ausposteparcel\Helper\Tracking $trackinghelper,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Json\Helper\Data $jsondata,
        \Magento\Framework\App\ResponseInterface $responseinterface
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->apimodel = $apimodel;
        $this->jsonHelper = $jsondata;
        $this->messageManager = $context->getMessageManager();
        $this->articlemodel = $articlemodel;
        $this->trackinghelper = $trackinghelper;
        $this->consignmentmodel = $consignmentmodel;
        $this->Date = $date;
        $this->responseinterface = $responseinterface;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $consignmentNumber = $this->getRequest()->getParam('consignment_number');
        $orderId = $this->getRequest()->getParam('order_id');

        $OrderToPrepare = $this->order->load($orderId);
        $consignmentModel = $this->consignmentmodel->load($orderId, 'order_id');
        $consignment = $consignmentModel->getData();
        $consignment_number = $consignment['consignment_number'];
        $eparcel_consignment_id = $consignment['eparcel_consignment_id'];
        $label_request_id = $consignment['label_request_id'];
        
        $getResponse = $this->apimodel->downloadLabel($consignment_number, $eparcel_consignment_id, $label_request_id);
        if ($getResponse === false) {
            $this->messageManager->addError("Please generate the labels");
            $this->_redirect("biztech_ausposteparcel/consignment/index");
        }
        $decodeData1 = $this->jsonHelper->jsonDecode($getResponse);

        if ($decodeData1['status'] == 'success') {
            $filename = $eparcel_consignment_id . ".pdf";
            $pdfurl = $decodeData1['pdf_url'];
            //Solve error: SSL operation failed with code by adding below option
            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );
            $content = file_get_contents($pdfurl, false, stream_context_create($arrContextOptions));
                
            $order = $this->order->load($orderId);
            $order->setIsLabelPrinted(1);
            $order->save();

            return $this->_sendUploadResponse($filename, $content);
        } else {
            $message = $decodeData1['message'];
//        if (isset($message) || $message != "") {}
            $this->messageManager->addError($message);
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
