<?php

namespace Meetanshi\ShippingRules\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Rule\Block\Actions;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Meetanshi\ShippingRules\Helper\Data;

class Rates extends Generic implements TabInterface
{
    protected $rendererFieldset;
    protected $actions;
    protected $helper;

    public function __construct(Context $context, Registry $registry, FormFactory $formFactory, Actions $actions, Fieldset $rendererFieldset, Data $helper)
    {
        $this->rendererFieldset = $rendererFieldset;
        $this->actions = $actions;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory);
    }

    public function getTabLabel()
    {
        return __('Shipping Rates');
    }

    public function getTabTitle()
    {
        return __('Shipping Rates');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_shippingrules_rule');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fldRate = $form->addFieldset('rate', ['legend' => __('Shipping Rates')]);
        $fldRate->addField('calculation', 'select', [
            'label' => __('Rate Calculation Type'),
            'name' => 'calculation',
            'options' => $this->helper->getCalculations(),
        ]);
        $fldRate->addField('rate_base', 'text', [
            'label' => __('Shipping Rate Per Order'),
            'name' => 'rate_base',
        ]);
        $fldRate->addField('rate_fixed', 'text', [
            'label' => __('Fixed Shipping Rate per Product'),
            'name' => 'rate_fixed',
        ]);

        $fldRate->addField('rate_percent', 'text', [
            'label' => __('Percentage Shipping Rate per Product'),
            'name' => 'rate_percent',
            'note' => __('Without discounts percentage of original product cart price is taken.'),
        ]);

        $fldRate->addField('handling_fee', 'text', [
            'label' => __('Handling Shipping Rate in Percentage'),
            'name' => 'handling_fee',
            'note' => __('The percentage will be added or deducted from the shipping rate.'),
        ]);

        $form->setValues($model->getData());
        $form->addValues(['id' => $model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
