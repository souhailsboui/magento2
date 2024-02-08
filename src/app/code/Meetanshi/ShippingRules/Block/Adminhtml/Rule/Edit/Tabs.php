<?php

namespace Meetanshi\ShippingRules\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

class Tabs extends WidgetTabs
{
    protected $registry;

    public function __construct(Registry $registry, Context $context, EncoderInterface $jsonEncoder, Session $authSession)
    {
        $this->registry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('shippingrules_rule_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rules Configuration'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', [
            'label' => __('General'),
            'title' => __('General'),
            'content' => $this->getLayout()->createBlock('\Meetanshi\ShippingRules\Block\Adminhtml\Rule\Edit\Tab\General')->toHtml(),
        ]);

        $this->addTab('rates', [
            'label' => __('Shipping Rates'),
            'title' => __('Shipping Rates'),
            'content' => $this->getLayout()->createBlock('\Meetanshi\ShippingRules\Block\Adminhtml\Rule\Edit\Tab\Rates')->toHtml(),
        ]);

        $this->addTab('products', [
            'label' => __('Products Conditions'),
            'title' => __('Products Conditions'),
            'content' => $this->getLayout()->createBlock('\Meetanshi\ShippingRules\Block\Adminhtml\Rule\Edit\Tab\Products')->toHtml(),
        ]);

        $this->addTab('conditions', [
            'label' => __('Shipping Address Conditions'),
            'title' => __('Shipping Address Conditions'),
            'content' => $this->getLayout()->createBlock('\Meetanshi\ShippingRules\Block\Adminhtml\Rule\Edit\Tab\Conditions')->toHtml(),
        ]);

        $this->addTab('dayinfo', [
            'label' => __('Days & Time'),
            'title' => __('Days & Time'),
            'content' => $this->getLayout()->createBlock('\Meetanshi\ShippingRules\Block\Adminhtml\Rule\Edit\Tab\DayInfo')->toHtml(),
        ]);

        $this->_updateActiveTab();

        return parent::_beforeToHtml();
    }

    protected function _updateActiveTab()
    {
        $tabId = $this->getRequest()->getParam('tab');
        if ($tabId) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if ($tabId) {
                $this->setActiveTab($tabId);
            }
        } else {
            $this->setActiveTab('main');
        }
    }
}
