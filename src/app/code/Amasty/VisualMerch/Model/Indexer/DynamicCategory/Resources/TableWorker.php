<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Indexer\DynamicCategory\Resources;

use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\CategoryIndex as IndexResource;
use Exception;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\Table\Strategy as IndexerTableStrategy;

class TableWorker
{
    /**
     * @var IndexResource
     */
    private $indexResource;

    /**
     * @var IndexerTableStrategy
     */
    private $indexerTableStrategy;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    public function __construct(
        IndexResource $indexResource,
        IndexerTableStrategy $indexerTableStrategy,
        ActiveTableSwitcher $activeTableSwitcher
    ) {
        $this->indexResource = $indexResource;
        $this->indexerTableStrategy = $indexerTableStrategy;
        $this->activeTableSwitcher = $activeTableSwitcher;
    }

    public function getIdxTable(): string
    {
        $this->indexerTableStrategy->setUseIdxTable(true);
        return $this->indexResource->getTableName(
            $this->indexerTableStrategy->prepareTableName(IndexResource::REPLICA_TABLE)
        );
    }

    public function insert(array $data, array $fieldsToUpdate = []): int
    {
        if ($data) {
            return $this->getConnection()->insertOnDuplicate(
                $this->indexResource->getTableName($this->getIdxTable()),
                $data,
                $fieldsToUpdate
            );
        }

        return 0;
    }

    public function update(array $updateData, array $conditions): void
    {
        $this->getConnection()->update($this->getIdxTable(), $updateData, $conditions);
    }

    public function clearReplica(): void
    {
        $this->getConnection()->truncateTable(
            $this->indexResource->getTableName(IndexResource::REPLICA_TABLE)
        );
    }

    public function createTemporaryTable(): void
    {
        $this->getConnection()->createTemporaryTableLike(
            $this->getIdxTable(),
            $this->indexResource->getTableName(IndexResource::REPLICA_TABLE),
            true
        );

        $this->getConnection()->delete($this->getIdxTable());
    }

    /**
     * @throws Exception
     */
    public function syncDataFull(): void
    {
        $this->syncData($this->indexResource->getTableName(IndexResource::REPLICA_TABLE));
    }

    /**
     * @param array $condition
     * @throws Exception
     */
    public function syncDataPartial(array $condition)
    {
        $this->syncData($this->indexResource->getTableName(IndexResource::MAIN_TABLE), $condition);
    }

    public function switchTables(): void
    {
        $this->activeTableSwitcher->switchTable(
            $this->getConnection(),
            [$this->indexResource->getTableName(IndexResource::MAIN_TABLE)]
        );
    }

    private function getConnection(): AdapterInterface
    {
        return $this->indexResource->getConnection();
    }

    /**
     * @param string $destinationTable
     * @param array $condition
     * @throws Exception
     */
    private function syncData(string $destinationTable, array $condition = []): void
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $connection->delete($destinationTable, $condition);
            $this->insertFromTable(
                $this->getIdxTable(),
                $destinationTable
            );
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    private function insertFromTable(string $sourceTable, string $destTable): void
    {
        $sourceColumns = array_keys($this->getConnection()->describeTable($sourceTable));
        $targetColumns = array_keys($this->getConnection()->describeTable($destTable));

        $select = $this->getConnection()->select()->from($sourceTable, $sourceColumns);

        $this->getConnection()->query($this->getConnection()->insertFromSelect($select, $destTable, $targetColumns));
    }
}
