<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MassAssignConsignment extends Action
{
    public $order;
    public $consignmentmodel;
    public $ausposteParcelInfoHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->order = $order;
        $this->consignmentmodel = $consignmentmodel;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam('order_consignment');
        if (!isset($ids)) {
            $this->messageManager->addError(__('Please Select Item(s)'));
        } else {
            try {
                $success = 0;
                $consignmentNumbers = array();
                $dispatchedConsignment = array();
                $assignedConsignment = array();
                $createShipment = array();
                $labelCreate = array();
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
                        $error = __('Order #%1: Does Not Have Consignment', $incrementId);
                        $this->messageManager->addError($error);
                    } else {
                        try {
                            $consignmentData = $this->ausposteParcelInfoHelper->getConsignment($orderId, $consignmentNumber);
                            $consignment = $this->consignmentmodel->load($consignmentData['consignment_id'], 'consignment_id');

                            if ($consignmentData['eparcel_consignment_id'] == null) {
                                $createShipment[] = $consignmentNumber;
                            } elseif ($consignmentData['is_label_created'] == 0) {
                                $labelCreate[] = $consignmentNumber;
                            } else {
                                $label = null;
                                if (isset($consignmentData['manifest_number']) && $consignmentData['manifest_number'] != null) {
                                    $label = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest')->load($consignmentData['manifest_number'], 'manifest_number')->getData('label');
                                    if ($label != null) {
                                        $dispatchedConsignment[] = $consignmentNumber;
                                        continue;
                                    }
                                }
                                $success++;
                                $consignmentNumbers[] = $consignmentNumber;
                                $orderNumbers[$consignmentNumber] = $orderId;
                                $updateData = array('is_next_manifest' => 1);

                                $consignment->addData($updateData);
                                $consignment->setConsignmentId($consignmentData['consignment_id'])->save();
                                $assignedConsignment[] = $consignmentNumber;
                            }
                        } catch (\Exception $e) {
                            $error = __('Consignment #%1, Error: %s', $consignmentNumber, $e->getMessage());
                            $this->messageManager->addError($error);
                        }
                    }
                }

                if ($orderId > 0 && $success > 0) {
                    $manifest_collection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest')->getCollection()->addFieldToFilter('despatch_date', null)->getLastItem();
                    if ($manifest_collection->getDespatchDate() == '' && $manifest_collection->getDespatchDate() == null && $manifest_collection->hasData()) {
                        $manifestNumber = $manifest_collection->getManifestNumber();
                        if ($manifestNumber) {
                            foreach ($consignmentNumbers as $consignmentNumber) {
                                $consignmentData = $this->ausposteParcelInfoHelper->getConsignment($orderNumbers[$consignmentNumber], $consignmentNumber);
                                $updateData = array('manifest_number' => $manifestNumber);
                                $consignment = $this->consignmentmodel->load($consignmentData['consignment_id'])->addData($updateData);
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
                    } else {
                        $manifest_collection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest')->getCollection()->getLastItem();
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
                        $manifest = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest');

                        $insertData = array(
                            'manifest_number' => $manifestNumber
                        );
                        $manifest->setData($insertData);
                        try {
                            $manifest->save()->getManifestId();

                            $manifest_collection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest')->getCollection()->addFieldToFilter('despatch_date', null)->getLastItem();

                            if ($manifest_collection->getDespatchDate() == '' || $manifest_collection->getDespatchDate() == null) {
                                $manifestNumber = $manifest_collection->getManifestNumber();
                                if ($manifestNumber) {
                                    foreach ($consignmentNumbers as $consignmentNumber) {
                                        $consignmentData = $this->ausposteParcelInfoHelper->getConsignment($orderNumbers[$consignmentNumber], $consignmentNumber);
                                        $updateData = array('manifest_number' => $manifestNumber);
                                        $consignment = $this->consignmentmodel->load($consignmentData['consignment_id'])->addData($updateData);
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
                        } catch (\Exception $e) {
                            $error = __('Cannot Create Manifest, Error: ') . $e->getMessage();
                            $this->messageManager->addError($error);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        if (!empty($createShipment)) {
            $noticeMsg = __('Consignment %1: Required to Generated Shipment And Need To Generate Labels.', implode(',', $createShipment));
            $this->messageManager->addNotice($noticeMsg);
        }
        if (!empty($labelCreate)) {
            $noticeMsg = __('Consignment %1: Has Generated Shipment Just Need To Generate The Label.', implode(',', $labelCreate));
            $this->messageManager->addNotice($noticeMsg);
        }
        if (!empty($dispatchedConsignment)) {
            $successmsg = __('Consignment %1: should not be added in Current Manifest as it is Dispatched', implode(',', $dispatchedConsignment));
            $this->messageManager->addError($successmsg);
        }
        if (!empty($assignedConsignment)) {
            $successmsg = __('Consignment %1: Successfully Assigned', implode(',', $assignedConsignment));
            $this->messageManager->addSuccess($successmsg);
        }
        $this->_redirect('*/*/index');
    }
}
