<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class OutStockBottom extends SortAbstract implements SortInterface
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return __("Move out of stock to bottom");
    }

    /**
     * @param Collection $collection
     * @return Collection
     */
    public function sort(Collection $collection)
    {
        if (!$this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            return $collection;
        }
        parent::sort($collection);
        $collection->getSelect()->joinLeft(
            ['stock_status' => $collection->getResource()->getTable('cataloginventory_stock_status')],
            'stock_status.product_id = e.entity_id and stock_status.stock_id = "'.  $this->getStockId() .'"',
            []
        );

        $collection->getSelect()
            ->order('stock_status.stock_status ' . $collection::SORT_ORDER_DESC)
            ->order('e.entity_id ' . $collection::SORT_ORDER_ASC);

        return $collection;
    }
}
