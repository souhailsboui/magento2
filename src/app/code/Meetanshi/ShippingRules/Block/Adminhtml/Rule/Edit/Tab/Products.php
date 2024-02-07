<?php

namespace Meetanshi\ShippingRules\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Rule\Block\Actions;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Rule\Model\Condition\AbstractCondition;

class Products extends Generic implements TabInterface
{
    protected $rendererFieldset;
    protected $actions;

    const SHIPPING_RULE_ACTIONS_FIELDSET_NAMESPACE = 'rule_actions_fieldset';

    public function __construct(Context $context, Registry $registry, FormFactory $formFactory, Actions $actions, Fieldset $rendererFieldset)
    {
        $this->rendererFieldset = $rendererFieldset;
        $this->actions = $actions;
        parent::__construct($context, $registry, $formFactory);
    }

    public function getTabLabel()
    {
        return __('Products Conditions');
    }

    public function getTabTitle()
    {
        return __('Products Conditions');
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

        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/*/newActionHtml/form/rule_actions_fieldset')
        );

        $fieldset = $form->addFieldset(
            'rule_actions_fieldset',
            [
                'legend' => __(
                    'Select products or leave blank for all products.'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'actions',
            'text',
            ['name' => 'actions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->actions
        );

        $this->setActionFormName($model->getActions(), self::SHIPPING_RULE_ACTIONS_FIELDSET_NAMESPACE, 'edit_form');

        $form->setValues($model->getData());
        $form->addValues(['id' => $model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function setActionFormName(
        AbstractCondition $actions,
        $jsFormName,
        $formName
    ) {
        $actions->setFormName($formName);
        $actions->setJsFormObject($jsFormName);

        if ($actions->getActions() && is_array($actions->getActions())) {
            foreach ($actions->getActions() as $condition) {
                $this->setActionFormName($condition, $jsFormName, $formName);
            }
        }
    }
}
