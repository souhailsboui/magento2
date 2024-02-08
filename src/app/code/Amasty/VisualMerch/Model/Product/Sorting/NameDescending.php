<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class NameDescending extends SortAbstract implements SortInterface
{
    /**
     * @return string
     */
    protected function getSortField()
    {
        return 'name';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __("Name: Descending");
    }

    /**
     * @param Collection $collection
     * @return Collection
     */
    public function sort(Collection $collection)
    {
        parent::sort($collection);
        $collection->addAttributeToSelect($this->getSortField(), true);
        $collection->addOrder($this->getSortField(), $this->descOrder());
        return $collection;
    }
}
