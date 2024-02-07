<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Observer\DynamicCategory;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\CategoryProcessor;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Indexer\SyncIndexedData;
use Amasty\VisualMerch\Model\ResourceModel\Product as StaticPositionResource;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Category\Product\Processor as CategoryProductProcessor;
use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReindexCategory implements ObserverInterface
{
    /**
     * @var StaticPositionResource
     */
    private $productPositionDataResource;

    /**
     * @var ResourceCategory
     */
    private $resourceCategory;

    /**
     * @var CategoryProcessor
     */
    private $categoryProcessor;

    /**
     * @var SyncIndexedData
     */
    private $syncIndexedData;

    /**
     * @var CategoryProductProcessor
     */
    private $categoryProductProcessor;

    public function __construct(
        StaticPositionResource $productPositionDataResource,
        ResourceCategory $resourceCategory,
        CategoryProcessor $categoryProcessor,
        SyncIndexedData $syncIndexedData,
        CategoryProductProcessor $categoryProductProcessor
    ) {
        $this->productPositionDataResource = $productPositionDataResource;
        $this->resourceCategory = $resourceCategory;
        $this->categoryProcessor = $categoryProcessor;
        $this->syncIndexedData = $syncIndexedData;
        $this->categoryProductProcessor = $categoryProductProcessor;
    }

    public function execute(Observer $observer)
    {
        /** @var Category $category */
        if ($category = $observer->getEvent()->getCategory()) {
            $productsPositionBefore = $category->getProductsPosition();
            $this->productPositionDataResource->saveProductPositionData($category);

            if ($category->dataHasChangedFor('amasty_dynamic_conditions')) {
                $this->categoryProcessor->reindexRow($category->getId());
                $productsPositionAfter = $this->resourceCategory->getProductsPosition($category);
                $this->modifyAffectedProductIds($category, $productsPositionBefore, $productsPositionAfter);
            } elseif ($category->dataHasChangedFor('amasty_category_product_sort')
                && !$this->categoryProcessor->isIndexerScheduled()
            ) {
                $this->syncIndexedData->execute([(int)$category->getId()]);
                $this->categoryProductProcessor->reindexRow($category->getId());
                // in case when only sorting changed, products before stay after
                // but need mark them as affected , for properly update position data in catalogsearch_fulltext index
                $this->modifyAffectedProductIds($category, [], $productsPositionBefore);
            }
        }
    }

    /**
     * Modify affected product ids for run catalogsearch_fulltext indexer.
     * Merge affected product ids from magento with ours.
     *
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Category::addCommitCallback
     * @see \Magento\Catalog\Model\ResourceModel\Category::_saveCategoryProducts
     */
    private function modifyAffectedProductIds(
        Category $category,
        array $productsPositionBefore,
        array $productsPositionAfter
    ): void {
        $insert = array_diff_key($productsPositionAfter, $productsPositionBefore);
        $delete = array_diff_key($productsPositionBefore, $productsPositionAfter);
        $update = array_intersect_key($productsPositionAfter, $productsPositionBefore);
        $update = array_diff_assoc($update, $productsPositionBefore);

        $oldAffected = $category->getAffectedProductIds() ?? [];
        $newAffected = array_keys($insert + $delete + $update);

        $category->setAffectedProductIds(array_unique(array_merge($oldAffected, $newAffected)));
    }
}
