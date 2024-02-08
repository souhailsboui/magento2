<?php

namespace Meetanshi\ShippingRules\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Rule\Block\Conditions as RuleConditions;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Rule\Model\Condition\AbstractCondition;

class Conditions extends Generic implements TabInterface
{
    protected $rendererFieldset;
    protected $conditions;
    protected $registry;

    const SHIPPING_RULE_CONDITIONS_FIELDSET_NAMESPACE = 'rule_conditions_fieldset';

    public function __construct(Context $context, Registry $registry, FormFactory $formFactory, RuleConditions $conditions, Fieldset $rendererFieldset)
    {
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        $this->registry = $registry;
        parent::__construct($context, $registry, $formFactory);
    }

    public function getTabLabel()
    {
        return __('Shipping Address Conditions');
    }

    public function getTabTitle()
    {
        return __('Shipping Address Conditions');
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
        $model = $this->registry->registry('current_shippingrules_rule');
        $form = $this->_formFactory->create();

        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/*/newConditionHtml/form/rule_conditions_fieldset')
        );

        $fieldset = $form->addFieldset(
            'rule_conditions_fieldset',
            [
                'legend' => __(
                    'Apply the rule only if the following conditions are met (leave blank for all products).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->conditions
        );

        $this->setConditionFormName($model->getConditions(), self::SHIPPING_RULE_CONDITIONS_FIELDSET_NAMESPACE);

        $form->setValues($model->getData());
        $form->addValues(['id' => $model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function setConditionFormName(
        AbstractCondition $conditions,
        $jsFormName,
        $formName = null
    ) {
        if ($formName) {
            $conditions->setFormName($formName);
        }
        $conditions->setJsFormObject($jsFormName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $jsFormName, $formName);
            }
        }
    }
}
