<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Block\Adminhtml\Conditions;

use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditions;

    /**
     * @var \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider
     */
    private $adminhtmlDataProvider;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $adminhtmlDataProvider,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->conditions = $conditions;
        $this->adminhtmlDataProvider = $adminhtmlDataProvider;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->adminhtmlDataProvider->initRule();
        $form = $this->addTabToForm($model);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @param \Amasty\VisualMerch\Model\Rule $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addTabToForm(
        \Amasty\VisualMerch\Model\Rule $model,
        $fieldsetId = 'conditions_fieldset',
        $formName = 'category_form'
    ) {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);
        $renderer = $this->getFieldsetRenderer()
            ->setTemplate('Amasty_VisualMerch::conditions/fieldset.phtml')
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset($fieldsetId, [])->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'note'  => __(
                    'Only the products matching the conditions will be present in the category after Apply.'
                    . ' Other unmatched products and manual sorting will be removed from the category.'
                )
            ]
        )->setRule($model)->setRenderer($this->conditions);

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName, $conditionsFieldSetId);
        return $form;
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     * @param string $jsFormName
     * @return void
     */
    private function setConditionFormName(
        \Magento\Rule\Model\Condition\AbstractCondition $conditions,
        $formName,
        $jsFormName
    ) {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }

    private function getFieldsetRenderer(): \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
    {
        $name = $this->getNameInLayout() . '_fieldset_base_element';
        $block = $this->getLayout()->getBlock($name);
        if (!$block) {
            $block = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Renderer\Fieldset::class,
                $name
            );
        }

        return $block;
    }
}
