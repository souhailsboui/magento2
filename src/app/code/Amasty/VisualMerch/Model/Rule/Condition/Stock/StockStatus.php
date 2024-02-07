<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition\Stock;

use Magento\Framework\DB\Select;

class StockStatus extends AbstractStock
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
        return __('In Stock')->render();
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

    /**
     * @return string
     */
    protected function _getAttributeCode()
    {
        return 'in_stock';
    }

    /**
     * @return $this
     */
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

    protected function getColumnExpression(Select $select): string
    {
        $fromTables = $select->getPart(Select::FROM);
        if ($this->isMsiEnabled()
            && $fromTables['stock_status']['tableName'] !== $this->getCatalogInventoryTable()
        ) {
            $stockStatusColumnName = 'is_salable';
        } else {
            $stockStatusColumnName = 'stock_status';
        }

        return $this->_getAlias() . '.' . $stockStatusColumnName;
    }
}
