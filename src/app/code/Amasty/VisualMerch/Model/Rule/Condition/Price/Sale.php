<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */
namespace Amasty\VisualMerch\Model\Rule\Condition\Price;

class Sale extends AbstractPrice
{
    /**
     * @var string
     */
    protected $_inputType = 'select';

    /**
     * @return string
     */
    public function getAttributeElementHtml()
    {
        return __('Is on Sale')->render();
    }

    /**
     * @return string
     */
    protected function _getAttributeCode()
    {
        return 'sale';
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    protected function _prepareValueOptions()
    {
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');

        $selectOptions = [
            ['value' => 1, 'label' => 'Yes'],
            ['value' => 0, 'label' => 'No']
        ];

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    protected function _getCondition()
    {
        if (!$this->_condition) {
            if ($this->getValue() && $this->getOperatorForValidate() == '==') {
                $conditionOperator = '<';
            } else {
                $conditionOperator = '>=';
            }

            $this->_condition = sprintf('%1$s.final_price %2$s %1$s.price', $this->_getAlias(), $conditionOperator);
        }

        return $this->_condition;
    }
}
