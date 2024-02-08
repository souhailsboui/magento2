<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\ResourceModel\DynamicCategory;

use Magento\Framework\App\ResourceConnection;

class CategoryTemporary
{
    public const MAIN_TABLE = 'amasty_merch_dynamic_category_products_temporary';

    public const HASH_COLUMN = 'hash';
    public const CATEGORY_ID_COLUMN = 'category_id';
    public const MATCHED_IDS_COLUMN = 'matched_ids';
    public const CREATED_AT_COLUMN = 'created_at';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function loadByHash(string $hash): ?string
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            $this->getMainTable(),
            [self::MATCHED_IDS_COLUMN]
        )->where(
            self::HASH_COLUMN . ' = ?',
            $hash
        );

        $result = $connection->fetchOne($select);

        return $result !== false ? $result : null;
    }

    public function loadByHashAndCategoryId(string $hash, int $categoryId): ?string
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            $this->getMainTable(),
            [self::MATCHED_IDS_COLUMN]
        )->where(
            self::HASH_COLUMN . ' = ?',
            $hash
        )->where(
            self::CATEGORY_ID_COLUMN . ' = ?',
            $categoryId
        );

        $result = $connection->fetchOne($select);

        return $result !== false ? $result : null;
    }

    public function insert(array $row): void
    {
        $this->resourceConnection->getConnection()->insert($this->getMainTable(), $row);
    }

    public function deleteOldData(string $createdAt): void
    {
        $this->resourceConnection->getConnection()->delete($this->getMainTable(), [
            self::CREATED_AT_COLUMN . ' <= ?' => $createdAt
        ]);
    }

    public function delete(string $hash, int $categoryId): void
    {
        $this->resourceConnection->getConnection()->delete($this->getMainTable(), [
            self::HASH_COLUMN . ' = ?' => $hash,
            self::CATEGORY_ID_COLUMN . ' = ?' => $categoryId
        ]);
    }

    public function getMainTable(): string
    {
        return $this->resourceConnection->getTableName(self::MAIN_TABLE);
    }
}
