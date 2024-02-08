<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Product\Sorting\ImprovedSorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;
use \Amasty\VisualMerch\Model\Product\Sorting\SortAbstract;
use \Amasty\VisualMerch\Model\Product\Sorting\SortInterface;

class DummyMethod extends SortAbstract implements SortInterface
{
    /**
     * @param Collection $collection
     * @return Collection
     */
    public function sort(Collection $collection)
    {
        parent::sort($collection);
        $this->getMethodInstance()->apply($collection, $this->getMethodDirection());
        $collection->setOrder($this->getMethodInstance()->getAlias(), $this->getMethodDirection());
        return $collection;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getMethodInstance()->getMethodLabel();
    }

    /**
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMethodInstance()
    {
        if (!is_object($this->getData('method_instance'))) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Requested sort method does not have proper method instance')
            );
        }
        return $this->getData('method_instance');
    }
}
