<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Biztech\Ausposteparcel\Model\Consignment;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Delete extends Action
{
    protected $_consignment;
    protected $order;
    protected $info;
    protected $consignmentmodelFactory;
    protected $manifestmodelFactory;
    protected $datahelper;
    // protected $messageManager;
    protected $_dir;

    // public function __construct(
    // Context $context, Consignment $consignment,\Magento\Sales\Api\Data\OrderInterface $order,\Biztech\Ausposteparcel\Helper\Info $info,ScopeConfigInterface $scopeconfiginterface, \Biztech\Ausposteparcel\Model\Cresource\Consignment\CollectionFactory $consignmentmodelFactory,\Biztech\Ausposteparcel\Model\Cresource\Manifest\CollectionFactory $manifestmodelFactory,\Biztech\Ausposteparcel\Helper\Data $datahelper, \Magento\Framework\Message\ManagerInterface $messageManager,\Magento\Framework\Filesystem\DirectoryList $dir
    // ) {
    public function __construct(
        Context $context,
        Consignment $consignment,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Helper\Info $info,
        ScopeConfigInterface $scopeconfiginterface,
        \Biztech\Ausposteparcel\Model\Cresource\Consignment\CollectionFactory $consignmentmodelFactory,
        \Biztech\Ausposteparcel\Model\Cresource\Manifest\CollectionFactory $manifestmodelFactory,
        \Biztech\Ausposteparcel\Helper\Data $datahelper,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        parent::__construct($context);
        $this->_consignment = $consignment;
        $this->order = $order;
        $this->info = $info;
        $this->consignmentmodelFactory = $consignmentmodelFactory;
        $this->manifestmodelFactory = $manifestmodelFactory;
        $this->datahelper = $datahelper;
        // $this->messageManager = $messageManager;
        $this->_dir = $dir;
        $this->messageManager = $context->getMessageManager();
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $consignmentNumber = $data['consignment_number'];

        $consignment = $this->info->getConsignment($data['order_id'], $consignmentNumber);
        try {
            if (sizeof($consignment) > 0) {
                $filename = $consignmentNumber . '.pdf';
                $filepath = $this->_dir->getPath('media') . 'ausposteParcel' . DIRECTORY_SEPARATOR . 'label' . DIRECTORY_SEPARATOR . 'consignment' . DIRECTORY_SEPARATOR . $filename;
                if (file_exists($filepath)) {
                    unlink($filepath);
                }

                $filepath2 = $this->_dir->getPath('media') . DIRECTORY_SEPARATOR . 'ausposteParcel' . DIRECTORY_SEPARATOR . 'label' . DIRECTORY_SEPARATOR . 'returnlabels' . DIRECTORY_SEPARATOR . $filename;
                if (file_exists($filepath2)) {
                    unlink($filepath2);
                }
                $this->datahelper->deleteConsignment($data['order_id'], $consignmentNumber);
                $this->datahelper->deleteManifest2($consignment['manifest_number']);

                $this->messageManager->addSuccess(__('Consignment #' . $consignmentNumber . ' has been deleted successfully.'));
                $this->_redirect("sales/order/view", array('order_id' => $data['order_id'], 'active_tab' => 'auspost_eparcel'));
            }
        } catch (\Exception $e) {
            $error = __('Could not delete consignment.') . $e->getMessage();
            $this->messageManager->addError($error);
            
            $this->datahelper->deleteManifest2($consignment['manifest_number']);
            if ($data['source'] == 'grid') {
                $this->_redirect("adminhtml/consignment/create", array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber));
            } else {
                $this->_redirect("sales/order/view", array('order_id' => $data['order_id'], 'active_tab' => 'auspost_eparcel'));
            }
        }
    }
}
