<?php
namespace Biztech\Ausposteparcel\Block\Adminhtml\Consignment;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Addarticle extends \Magento\Framework\View\Element\Template
{
    protected $order;
    protected $info;
    private $_objectManager;
    protected $urlinterface;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,\Magento\Backend\Model\UrlInterface $urlinterface, \Magento\Sales\Api\Data\OrderInterface $order, \Biztech\Ausposteparcel\Helper\Info $info, \Magento\Framework\ObjectManagerInterface $objectmanager)
    {
        $this->order = $order;
        $this->info = $info;
        $this->scopeconfiginterface = $context->getScopeConfig();
        $this->_objectManager = $objectmanager;
        $this->urlinterface = $urlinterface;
        parent::__construct($context);
    }
    public function getOrder()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        return $order_id;
    }

    public function getHeaderText()
    {
        $header = __('Add Article %1 for Consignment #' . $this->getRequest()->getParam('consignment_number'), $this->getRequest()->getParam('article_number'));
        return $header;
    }

    public function getBackUrl()
    {
        $source = $this->getRequest()->getParam('source');
        if ($source == 'grid') {
            return $this->getUrl('biztech_ausposteparcel/consignmentcreate/', ['order_id' => $this->getOrder(), 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'source' => 'grid']);
        } else {
            return $this->getUrl('sales/order/view', ['order_id' => $this->getOrder(), 'active_tab' => 'auspost_eparcel']);
        }
    }

    public function getSaveUrl()
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/newarticle/', ['order_id' => $this->getOrder(), 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'article_number' => $this->getRequest()->getParam('article_number')]);
        return $url;
    }

    public function getConsignment()
    {
        return $this->info->getConsignment($this->getRequest()->getParam('order_id'), $this->getRequest()->getParam('consignment_number'));
    }

    public function getArticle()
    {
        return $this->info->getArticle($this->getRequest()->getParam('order_id'), $this->getRequest()->getParam('consignment_number'), $this->getRequest()->getParam('article_number'));
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
