<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MassAssignPendingConsignment extends Action
{
    public $order;
    public $apimodel;
    public $consignmentmodel;
    public $ausposteParcelInfoHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Model\Api $apimodel,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->order = $order;
        $this->apimodel = $apimodel;
        $this->consignmentmodel = $consignmentmodel;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
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
            $manifestNumber = $this->getRequest()->getParam('manifest_number');
            try {
                $success = 0;
                $consignmentNumbers = array();
                if (!is_array($ids)) {
                    $ids = explode(',', $ids);
                }
                foreach ($ids as $id) {
                    $values = explode('_', $id);
                    $orderId = (int) ($values[0]);
                    $consignmentNumber = $values[1];
                    $order = $this->order->load($orderId);
                    $incrementId = $order->getIncrementId();
                    if ($consignmentNumber == '0') {
                        $error = __('Order #%s: does not have consignment', $incrementId);
                        $this->messageManager->addError($error);
                    } else {
                        try {
                            $success++;
                            $consignmentNumbers[] = $consignmentNumber;
                            $orderNumbers[$consignmentNumber] = $orderId;

                            $consignmentData = $this->ausposteParcelInfoHelper->getConsignment($orderId, $consignmentNumber);
                            $updateData = array('is_next_manifest' => 1);
                            $consignment = $this->consignmentmodel;
                            $consignment->load($consignmentData['consignment_id'])->addData($updateData);
                            $consignment->setConsignmentId($consignmentData['consignment_id'])->save();

                            $successmsg = __('Consignment #%1: Successfully Assigned', $consignmentNumber);
                            $this->messageManager->addSuccess($successmsg);
                        } catch (\Exception $e) {
                            $error = __('Consignment #%1, Error: %s', $consignmentNumber, $e->getMessage());
                            $this->messageManager->addError($error);
                        }
                    }
                }

                if ($orderId > 0 && $success > 0) {
                    $manifest_collection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest')->getCollection()->addFieldToFilter('manifest_number', $manifestNumber)->getData();
                    if ($manifest_collection[0]['despatch_date'] == '' || $manifest_collection[0]['despatch_date'] == null) {
                        if ($manifestNumber) {
                            foreach ($consignmentNumbers as $consignmentNumber) {
                                $consignmentData = $this->ausposteParcelInfoHelper->getConsignment($orderNumbers[$consignmentNumber], $consignmentNumber);

                                $updateData = array('manifest_number' => $manifestNumber);
                                $consignment = $this->consignmentmodel;
                                $consignment->load($consignmentData['consignment_id'])->addData($updateData);
                                $consignment->setConsignmentId($consignmentData['consignment_id'])->save();
                            }

                            $consignmentArticleCount = $this->ausposteParcelInfoHelper->getConsignmentArticleByManifestNumber($manifestNumber);
                            $numberOfArticles = (int) $consignmentArticleCount['numberOfArticles'];
                            $numberOfConsignments = (int) $consignmentArticleCount['numberOfConsignments'];
                            $helper = $this->_objectManager->get('Biztech\AusposteParcel\Helper\Data');
                            $helper->updateManifest($manifestNumber, $numberOfArticles, $numberOfConsignments);
                            $config_model = $this->_objectManager->get('\Magento\Framework\App\Config\Storage\WriterInterface');
                            $config_model->save('carriers/ausposteParcel/manifestSync', 0, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('biztech_ausposteparcel/manifestconsignments/index', array('manifest' => $this->getRequest()->getParam('manifest_number')));
    }
}
