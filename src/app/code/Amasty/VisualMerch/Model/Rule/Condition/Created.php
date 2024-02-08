<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition;

class Created extends Product
{
    /**
     * @var string
     */
    protected $_inputType = 'numeric';

    public function getAttributeElementHtml()
    {
        return __('Created (in days)');
    }

    protected function _getAttributeCode()
    {
        return 'created';
    }

    protected function _getCondition($alias, $valueField, $operator, $value)
    {
        return $this->getOperatorCondition(
            new \Zend_Db_Expr("datediff(now(), {$alias}.{$valueField})"),
            $operator,
            $value
        );
    }

    public function getAttribute()
    {
        return 'created_at';
    }

    public function getInputType()
    {
        return 'string';
    }

    public function getValueElementType()
    {
        return 'text';
    }
}
