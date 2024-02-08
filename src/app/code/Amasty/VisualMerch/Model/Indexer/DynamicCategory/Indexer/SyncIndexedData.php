<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Indexer\DynamicCategory\Indexer;

use Amasty\VisualMerch\Model\DynamicCategory\SortIds;
use Amasty\VisualMerch\Model\DynamicCategory\Store\GetStoresForRootCategory;
use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\CategoryIndex;
use Exception;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Psr\Log\LoggerInterface;

/**
 * Move data from index table into catalog_category_product.
 */
class SyncIndexedData
{
    /**
     * @var GetStoresForRootCategory
     */
    private $getStoresForRootCategory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var CategoryIndex
     */
    private $categoryIndex;

    /**
     * @var SortIds
     */
    private $sortIds;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        GetStoresForRootCategory $getStoresForRootCategory,
        ResourceConnection $resourceConnection,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryIndex $categoryIndex,
        SortIds $sortIds,
        LoggerInterface $logger
    ) {
        $this->getStoresForRootCategory = $getStoresForRootCategory;
        $this->resourceConnection = $resourceConnection;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryIndex = $categoryIndex;
        $this->sortIds = $sortIds;
        $this->logger = $logger;
    }

    public function execute(?array $categoryIds = null): void
    {
        foreach ($this->getCategories($categoryIds) as $categoryId => $categoryData) {
            $this->executeCategory($categoryId, $categoryData);
        }
    }

    /**
     * @param int $categoryId
     * @param array $categoryData ['amasty_category_product_sort' => value, 'path' => value]
     */
    private function executeCategory(int $categoryId, array $categoryData): void
    {
        $sortMethod = (int)$categoryData['amasty_category_product_sort'];
        $categoryPath = explode('/', (string)$categoryData['path']);
        if (!isset($categoryPath[1])) {
            return;
        }
        $rootCategoryId = (int)$categoryPath[1];

        $connection = $this->resourceConnection->getConnection();

        $connection->beginTransaction();
        try {
            $this->deleteOldProducts($categoryId);
            $productsData = [];
            foreach ($this->getStoresForRootCategory->execute($rootCategoryId) as $storeId) {
                $productsData += $this->getSortedProductData($categoryId, $storeId, $sortMethod);
            }
            $this->insertProducts($productsData);
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array ['category_id' => ['amasty_category_product_sort' => value, 'path' => value], ...]
     */
    private function getCategories(?array $categoryIds): array
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addAttributeToFilter('amlanding_is_dynamic', 1);
        $categoryCollection->addIsActiveFilter();
        if ($categoryIds !== null) {
            $categoryCollection->addIdFilter($categoryIds);
        }

        $categoryCollection->getSelect()->reset(Select::COLUMNS);
        $categoryCollection->getSelect()->columns(['entity_id', 'path']);
        $categoryCollection->addAttributeToSelect('amasty_category_product_sort', 'left');

        return $categoryCollection->getResource()->getConnection()->fetchAssoc($categoryCollection->getSelect());
    }

    private function deleteOldProducts(int $categoryId): void
    {
        $deleteQuery = $this->resourceConnection->getConnection()->select()->from(
            ['ccp' => $this->resourceConnection->getTableName('catalog_category_product')],
            []
        )->joinLeft(
            ['dcp' => $this->resourceConnection->getTableName(CategoryIndex::MAIN_TABLE)],
            sprintf(
                'ccp.product_id = dcp.%s AND ccp.category_id = dcp.%s',
                CategoryIndex::PRODUCT_ID_COLUMN,
                CategoryIndex::CATEGORY_ID_COLUMN
            ),
            []
        )->where(
            'ccp.category_id = ?',
            $categoryId
        )->where(
            sprintf('dcp.%s IS NULL', CategoryIndex::PRODUCT_ID_COLUMN)
        )->deleteFromSelect('ccp');

        $this->resourceConnection->getConnection()->query($deleteQuery);
    }

    private function getSortedProductData(int $categoryId, int $storeId, int $sortMethod): array
    {
        $productIds = $this->categoryIndex->loadProductIds($categoryId, $storeId);
        if (empty($productIds)) {
            return [];
        }

        $productIds = $this->sortIds->execute($categoryId, $productIds, $storeId, $sortMethod);

        $rows = [];
        foreach ($productIds as $position => $productId) {
            $rows[$productId] = [
                'category_id' => $categoryId,
                'product_id' => $productId,
                'position' => $position
            ];
        }

        return $rows;
    }

    private function insertProducts(array $rows): void
    {
        if ($rows) {
            $this->resourceConnection->getConnection()->insertOnDuplicate(
                $this->resourceConnection->getTableName('catalog_category_product'),
                $rows,
                ['position']
            );
        }
    }
}
