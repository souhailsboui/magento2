<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Indexer\DynamicCategory\Action;

use Amasty\VisualMerch\Model\DynamicCategory\GetMatchedProductIds;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\CacheContext;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Indexer;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Resources\TableWorker;
use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\CategoryIndex;
use Amasty\VisualMerch\Model\RuleFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Store\Model\StoreManagerInterface;

class DoReindex
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetMatchedProductIds
     */
    private $getMatchedProductIds;

    /**
     * @var TableWorker
     */
    private $tableWorker;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var int
     */
    private $batchCount;

    /**
     * Limit for clear cache per once.
     * Need for avoid error with big data.
     * Ex. with varnish
     *
     * @var int
     */
    private $batchCacheCount;

    public function __construct(
        RuleFactory $ruleFactory,
        StoreManagerInterface $storeManager,
        GetMatchedProductIds $getMatchedProductIds,
        TableWorker $tableWorker,
        CategoryCollectionFactory $categoryCollectionFactory,
        CacheContext $cacheContext,
        EventManager $eventManager,
        int $batchCount = 1000,
        int $batchCacheCount = 100
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->storeManager = $storeManager;
        $this->getMatchedProductIds = $getMatchedProductIds;
        $this->tableWorker = $tableWorker;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->batchCount = $batchCount;
        $this->batchCacheCount = $batchCacheCount;
    }

    public function execute(?array $categoryIds = null, ?array $productIds = null): void
    {
        $rows = [];
        $count = 0;
        $amastyRule = $this->ruleFactory->create();

        foreach ($this->storeManager->getStores() as $store) {
            $storeId = (int) $store->getId();
            $categories = $this->getCategories((int) $store->getRootCategoryId(), $categoryIds);
            foreach ($categories as $categoryId => $dynamicConditions) {
                $amastyRule->setConditions(null);
                $amastyRule->setConditionsSerialized($dynamicConditions);
                $matchedProductIds = $this->getMatchedProductIds->execute(
                    $amastyRule->getConditions(),
                    $storeId,
                    $productIds
                );

                foreach ($matchedProductIds as $productId) {
                    $rows[] = [
                        CategoryIndex::PRODUCT_ID_COLUMN => $productId,
                        CategoryIndex::STORE_ID_COLUMN => $storeId,
                        CategoryIndex::CATEGORY_ID_COLUMN => $categoryId
                    ];
                    if (++$count > $this->batchCount) {
                        $this->tableWorker->insert($rows);
                        $count = 0;
                        $rows = [];
                    }
                    $this->registerEntities(Product::CACHE_TAG, [$productId]);
                }
                $this->registerEntities(Category::CACHE_TAG, [$categoryId]);
            }
        }

        $this->tableWorker->insert($rows);
        $this->cleanCache();
    }

    /**
     * @return array ['category_id' => 'conditions', ...]
     */
    private function getCategories(int $rootCategoryId, ?array $categoryIds): array
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addAttributeToFilter('amlanding_is_dynamic', 1);
        $categoryCollection->addFieldToFilter([
            ['attribute' => 'path', 'like' => '1/' . $rootCategoryId . '/%'],
            ['attribute' => 'path', 'eq' => '1/' . $rootCategoryId]
        ]);
        $categoryCollection->addIsActiveFilter();
        if ($categoryIds !== null) {
            $categoryCollection->addIdFilter($categoryIds);
        }

        $categoryCollection->getSelect()->reset(Select::COLUMNS);
        $categoryCollection->getSelect()->columns('entity_id');
        $categoryCollection->addAttributeToSelect('amasty_dynamic_conditions', 'left');

        return $categoryCollection->getResource()->getConnection()->fetchPairs($categoryCollection->getSelect());
    }

    private function registerEntities(string $cacheTag, array $ids): void
    {
        $this->cacheContext->registerEntities($cacheTag, $ids);
        if ($this->cacheContext->getSize() > $this->batchCacheCount) {
            $this->cleanCache();
            $this->cacheContext->flush();
        }
    }

    private function cleanCache(): void
    {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}
