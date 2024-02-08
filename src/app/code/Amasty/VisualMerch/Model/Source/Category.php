<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManager;

class Category
{
    public const SYSTEM_CATEGORY_ID = 1;
    public const ROOT_LEVEL = 1;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManager
     */
    private $storeManager;

    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManager $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return  array_replace(
            [__('-- Please Select --')],
            $this->getChildren(self::SYSTEM_CATEGORY_ID, self::ROOT_LEVEL)
        );
    }

    /**
     * @param int $parentCategoryId
     * @param int $level
     * @return array
     */
    private function getChildren($parentCategoryId, $level)
    {
        $options = [];
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToFilter('level', $level);
        $collection->addAttributeToFilter('parent_id', $parentCategoryId);
        $collection->addAttributeToFilter('is_active', 1);
        $collection->setOrder('position', 'asc');
        foreach ($collection as $category) {
            $options[$category->getId()] = str_repeat(". ", max(0, ($category->getLevel() - 1) * 3))
                . $category->getName();
            if ($category->hasChildren()) {
                $options = array_replace($options, $this->getChildren($category->getId(), $category->getLevel() + 1));
            }
        }
        return $options;
    }
}
