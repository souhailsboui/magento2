<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition\Stock;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\DB\Select;

class Qty extends AbstractStock
{
    /**
     * @var string
     */
    protected $_inputType = 'numeric';

    protected function getColumnExpression(Select $select): string
    {
        $fromTables = $select->getPart(Select::FROM);
        if ($this->isMsiEnabled()
            && $fromTables['stock_status']['tableName'] !== $this->getCatalogInventoryTable()
        ) {
            $qtyColumnName = 'quantity';
        } else {
            $qtyColumnName = 'qty';
        }

        return $this->_getAlias() . '.' . $qtyColumnName;
    }

    /**
     * @return string
     */
    public function getAttributeElementHtml()
    {
        return __('Qty')->render();
    }

    /**
     * @return string
     */
    protected function _getAttributeCode()
    {
        return 'qty';
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return string
     */
    public function getOperatorCondition($field, $operator, $value)
    {
        return sprintf(
            '(%s AND e.type_id = "%s")',
            parent::getOperatorCondition($field, $operator, $value),
            Type::TYPE_SIMPLE
        );
    }
}
