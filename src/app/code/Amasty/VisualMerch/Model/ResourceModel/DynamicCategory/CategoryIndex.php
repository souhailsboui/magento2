<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\ResourceModel\DynamicCategory;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class CategoryIndex
{
    public const MAIN_TABLE = 'amasty_merch_dynamic_category_products';
    public const REPLICA_TABLE = 'amasty_merch_dynamic_category_products_replica';

    public const CATEGORY_ID_COLUMN = 'category_id';
    public const STORE_ID_COLUMN = 'store_id';
    public const PRODUCT_ID_COLUMN = 'product_id';
    public const POSITION_COLUMN = 'position';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    public function getTableName(string $tableName): string
    {
        return $this->resourceConnection->getTableName($tableName);
    }

    public function loadProductIds(int $categoryId, int $storeId): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(self::MAIN_TABLE),
            [self::PRODUCT_ID_COLUMN]
        )->where(
            self::CATEGORY_ID_COLUMN . ' = ?',
            $categoryId
        )->where(
            self::STORE_ID_COLUMN . ' = ?',
            $storeId
        );

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }

    public function loadCategoryIds(int $productId, int $storeId): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(self::MAIN_TABLE),
            [self::CATEGORY_ID_COLUMN]
        )->where(
            self::STORE_ID_COLUMN . ' = ?',
            $storeId
        )->where(
            self::PRODUCT_ID_COLUMN . ' = ?',
            $productId
        );

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }
}
