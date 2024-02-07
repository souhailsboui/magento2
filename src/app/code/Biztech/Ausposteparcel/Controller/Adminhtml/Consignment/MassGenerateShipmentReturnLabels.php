<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MassGenerateShipmentReturnLabels extends Action
{
    public $order;
    public $consignmentmodel;
    public $apimodel;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Biztech\Ausposteparcel\Model\Api $apimodel
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->order = $order;
        $this->consignmentmodel = $consignmentmodel;
        $this->apimodel = $apimodel;
    }

    public function execute()
    {
        $getParams = $this->getRequest()->getParams();
        $ids = $getParams['order_consignment'];
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
            $consignmentIds = array();
            foreach ($order_ids as $key1 => $value1) {
                $orderId = $value1[0];
                $consignment = $this->consignmentmodel->load($orderId, 'order_id')->getData();
                if ($consignment['is_label_printed'] == 1 && $consignment['is_label_created'] == 1) {
                    $ignoreArray[] = $value1[1];
                } else {
                    $consignmentIds[] = $value1;
                    $cIds[] = $value1[1];
                    $orderIds[] = $value1[0];
                }
            }
            if (empty($consignmentIds)) {
                $this->messageManager->addError(__('There is no any consignment to generate shipment return label'));
                $this->_redirect('*/*/index');
            }
            $getResponse = $this->apimodel->massShipmentGenerate($consignmentIds, $returnLabels = 1);
            $this->jsonHelper = $this->_objectManager->get('Magento\Framework\Json\Helper\Data');
            $decodeResponse = $this->jsonHelper->jsonDecode($getResponse, true);
            if ($decodeResponse['status'] == 'error') {
                $responseMessage = $decodeResponse['message'][0]['message'];
                $this->messageManager->addError($responseMessage);
                $this->_redirect('*/*/index');
                return true;
            }

            if (!empty($ignoreArray)) {
                $ignoreConsignmentIds = implode(",", $ignoreArray);
                $message = __('Label already generated for consignments') . " " . $ignoreConsignmentIds;
                $this->messageManager->addError($message);
            }

            $returnLabelsData = array();
            if ($decodeResponse['shipments']) {
                foreach ($decodeResponse['shipments'] as $key => $value) {
                    $eParcelShipmentId = $value['shipment_id'];
                    $ShipmentGeneratedconsignmentId [] = $value['shipment_reference'];
                    if (isset($value['items'])) {
                        foreach ($value['items'] as $itemKey => $itemValue) {
                            $returnLabelsData[$value['shipment_reference']]['eparce_return_shipment_id'] = $value['shipment_id'];

                            $returnLabelsData[$value['shipment_reference']]['eparcel_return_article_id'] = $itemValue['tracking_details']['article_id'];
                            $returnLabelsData[$value['shipment_reference']]['eparcel_return_consignment_id'] = $itemValue['tracking_details']['consignment_id'];
                        }
                    }
                }

                //save Data
                $this->saveReturnLabelData($returnLabelsData);
                foreach ($consignmentIds as $key => $value) {
                    $successConsignmentIds[] = $value[1];
                }
                $cIds = implode(',', $successConsignmentIds);
                $message = 'Return Shipment has been generated successfully for consignment(s) number(s) ' . $cIds;
                $this->messageManager->addSuccess($message);
                $this->_redirect('*/*/index');
                return true;
            }
        }
    }

    public function saveReturnLabelData($returnLabelsData)
    {
        foreach ($returnLabelsData as $key => $value) {
            $timestamp = time();
            $date = date('Y-m-d H:i:s', $timestamp);

            //save consignment data in consignment table
            $data = array('eparcel_consignment_id' => $value['eparcel_consignment_id'], 'modify_date' => $date);
            $consignmentModel = $this->consignmentmodel->load($key, 'consignment_number');
            $orderId = $consignmentModel->getOrderId();
            $orderToPrepare = $this->order->load($orderId);
            //Save eParcel Shipment Id into sales order table
            $orderToPrepare->setEparcelReturnlabelShippingId($value['eparce_return_shipment_id'])->save();
            $orderToPrepare->setIsLabelGenerated(1)->save();
        }
    }
}
