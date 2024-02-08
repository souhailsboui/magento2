<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Setup\Patch\DeclarativeSchemaApplyBefore;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Clear index tables for correct apply new primary keys.
 * Prevent duplicate key.
 */
class ClearIndexTableForNewKey implements DataPatchInterface
{
    private const INDEX_TABLE = 'amasty_merchandiser_product_index_eav';
    private const INDEX_REPLICA_TABLE = 'amasty_merchandiser_product_index_eav_replica';
    private const INDEX_TMP_TABLE = 'amasty_merchandiser_product_index_eav_tmp';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(ResourceConnection $resourceConnection, IndexerRegistry $indexerRegistry)
    {
        $this->resourceConnection = $resourceConnection;
        $this->indexerRegistry = $indexerRegistry;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    /**
     * @return ClearIndexTableForNewKey
     */
    public function apply()
    {
        if ($this->isNeedApply()) {
            $this->resourceConnection->getConnection()->truncateTable(
                $this->resourceConnection->getTableName(self::INDEX_TABLE)
            );
            $this->resourceConnection->getConnection()->truncateTable(
                $this->resourceConnection->getTableName(self::INDEX_REPLICA_TABLE)
            );
            $this->resourceConnection->getConnection()->truncateTable(
                $this->resourceConnection->getTableName(self::INDEX_TMP_TABLE)
            );
            if ($indexer = $this->indexerRegistry->get('merchandiser_product_attribute')) {
                $indexer->invalidate();
            }
        }

        return $this;
    }

    private function isNeedApply(): bool
    {
        $tableName = $this->resourceConnection->getTableName(self::INDEX_TABLE);
        return $this->resourceConnection->getConnection()->isTableExists($tableName)
            && $this->resourceConnection->getConnection()->tableColumnExists($tableName, 'source_id');
    }
}
