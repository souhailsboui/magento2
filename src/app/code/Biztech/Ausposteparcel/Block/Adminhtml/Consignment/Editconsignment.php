<?php
namespace Biztech\Ausposteparcel\Block\Adminhtml\Consignment;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Editconsignment extends \Magento\Framework\View\Element\Template
{
    protected $order;
    protected $info;
    // protected $scopeconfiginterface;
    private $_objectManager;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Sales\Api\Data\OrderInterface $order, \Magento\Framework\ObjectManagerInterface $objectmanager, \Biztech\Ausposteparcel\Helper\Info $info)
    {
        $this->order = $order;
        $this->info = $info;
        $this->scopeconfiginterface = $context->getScopeConfig();
        $this->_objectManager = $objectmanager;
        parent::__construct($context);
    }

    public function getOrder()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        return $order_id;
    }

    public function getHeaderText()
    {
        $header = "Edit Consignment #".$this->getRequest()->getParam('consignment_number');
        
        // $header = 'Edit Consignment #%s'.$this->getRequest()->getParam('consignment_number');
        return $header;
    }

    public function getBackUrl()
    {
        $source = $this->getRequest()->getParam('source');
        if ($source == 'grid') {
            return $this->getUrl('biztech_ausposteparcel/consignmentcreate/', array('order_id' => $this->getRequest()->getParam('order_id'), 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'source' => 'grid'));
        } else {
            return $this->getUrl('sales/order/view', array('order_id' => $this->getRequest()->getParam('order_id'), 'active_tab' => 'auspost_eparcel'));
        }
    }

    public function getSaveUrl()
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/updateconsignment/', array('order_id' => $this->getRequest()->getParam('order_id'), 'consignment_number' => $this->getRequest()->getParam('consignment_number')));
        return $url;
    }

    public function getConsignment()
    {
        return $this->info->getConsignment($this->getRequest()->getParam('order_id'), $this->getRequest()->getParam('consignment_number'));
    }

    public function getArticles()
    {
        return $this->info->getArticles($this->getRequest()->getParam('order_id'), $this->getRequest()->getParam('consignment_number'));
    }
    public function getStoreConfig($configValue)
    {
        return $this->scopeconfiginterface->getValue($configValue);
    }
    public function getobjectManager()
    {
        return $this->_objectManager;
    }
}
