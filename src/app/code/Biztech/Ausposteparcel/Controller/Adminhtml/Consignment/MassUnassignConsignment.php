<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MassUnassignConsignment extends Action
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
            $this->messageManager->addError(__('Please select item(s)'));
        } else {
            try {
                $success = 0;
                $consignmentNumbers = array();
                $dispatchedConsignment = array();
                $unassignedConsignment = array();
                $alredyUnassigned = array();
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
                        $error = __('Order #%1: does not have consignment', $incrementId);
                        $this->messageManager->addError($error);
                    } else {
                        try {
                            $consignmentData = $this->ausposteParcelInfoHelper->getConsignment($orderId, $consignmentNumber);
                            $consignment = $this->consignmentmodel->load($consignmentData['consignment_id'], 'consignment_id');

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
                            if($consignment->getIsNextManifest()==0 || $consignment->getIsNextManifest()==null) {
                                $alredyUnassigned[] = $consignmentNumber;
                                continue;
                            }
                            $updateData = array('manifest_number' => '', 'is_next_manifest' => 0);

                            $consignment->addData($updateData);
                            $consignment->setConsignmentId($consignmentData['consignment_id'])->save();
                            $unassignedConsignment[] = $consignmentNumber;
                        } catch (\Exception $e) {
                            $error = __('Consignment #%1, Error: %s', $consignmentNumber, $e->getMessage());
                            $this->messageManager->addError($error);
                        }
                    }
                }

                if ($success > 0) {
                    $manifest_collection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest')->getCollection()->addFieldToFilter('despatch_date', null)->getLastItem();

                    if ($manifest_collection->getDespatchDate() == '' || $manifest_collection->getDespatchDate() == null) {
                        $manifestNumber = $manifest_collection->getManifestNumber();
                        if ($manifestNumber) {
                            $consignmentArticleCount = $this->ausposteParcelInfoHelper->getConsignmentArticleByManifestNumber($manifestNumber);
                            $numberOfArticles = (int) $consignmentArticleCount['numberOfArticles'];
                            $numberOfConsignments = (int) $consignmentArticleCount['numberOfConsignments'];
                            $helper = $this->_objectManager->get('Biztech\AusposteParcel\Helper\Data');
                            $helper->updateManifest($manifestNumber, $numberOfArticles, $numberOfConsignments);
                            $config_model = $this->_objectManager->get('\Magento\Framework\App\Config\Storage\WriterInterface');
                            $config_model->save('carriers/ausposteParcel/manifestSync', 0, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
                            //$helper->deleteManifest2($manifestNumber);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        if (!empty($dispatchedConsignment)) {
            $successmsg = __('Consignment %1: should not be removed from Manifest as it is Dispatched', implode(',', $dispatchedConsignment));
            $this->messageManager->addNotice($successmsg);
        }
        if (!empty($alredyUnassigned)) {
            $successmsg = __('Consignment %1: Not added in Manifest. You have to first Add it to Current Manifest.', implode(',', $alredyUnassigned));
            $this->messageManager->addNotice($successmsg);
        }
        if (!empty($unassignedConsignment)) {
            $successmsg = __('Consignment %1: Successfully Unassigned', implode(',', $unassignedConsignment));
            $this->messageManager->addSuccess($successmsg);
        }
        $this->_redirect('*/*/index');
    }
}