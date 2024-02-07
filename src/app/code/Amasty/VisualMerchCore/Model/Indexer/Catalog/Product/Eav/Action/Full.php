<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Eav\Action;

use Amasty\VisualMerchCore\Model\ResourceModel\Product\Indexer\Eav\Adapter;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\BatchIteratorInterface;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\BatchProviderInterface;
use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Eav\IndexAdapter;

class Full
{
    public const BATCH_SIZE = 1000;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var QueryGenerator|null
     */
    private $batchQueryGenerator;

    /**
     * @var IndexAdapter
     */
    private $indexAdapter;

    public function __construct(
        MetadataPool $metadataPool,
        BatchProviderInterface $batchProvider,
        ActiveTableSwitcher $activeTableSwitcher,
        QueryGenerator $batchQueryGenerator,
        IndexAdapter $indexAdapter
    ) {
        $this->metadataPool = $metadataPool;
        $this->batchProvider = $batchProvider;
        $this->activeTableSwitcher = $activeTableSwitcher;
        $this->batchQueryGenerator = $batchQueryGenerator;
        $this->indexAdapter = $indexAdapter;
    }

    /**
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(): void
    {
        try {
            $connection = $this->getIndexer()->getConnection();
            $mainTable = $this->activeTableSwitcher->getAdditionalTableName($this->getIndexer()->getMainTable());
            $connection->truncateTable($mainTable);

            $queries = $this->getBatchQueries();

            foreach ($queries as $query) {
                $entityIds = $connection->fetchCol($query);
                if (!empty($entityIds)) {
                    $entityIds = $this->indexAdapter->processRelations($entityIds, true);
                    $this->getIndexer()->reindexEntities($entityIds);
                    $this->syncData($mainTable);
                }
            }
            $this->activeTableSwitcher->switchTable(
                $this->getIndexer()->getConnection(),
                [$this->getIndexer()->getMainTable()]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @return Adapter
     * @throws LocalizedException
     */
    public function getIndexer(): Adapter
    {
        return $this->indexAdapter->getIndexer();
    }

    /**
     * @return BatchIteratorInterface
     * @throws LocalizedException
     */
    private function getBatchQueries()
    {
        $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);

        $select = $this->getIndexer()->getConnection()->select();
        $select->distinct(true);
        $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());

        return $this->batchQueryGenerator->generate(
            $entityMetadata->getIdentifierField(),
            $select,
            self::BATCH_SIZE,
            BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
        );
    }

    /**
     * @param string $destinationTable
     * @param array $ids
     * @throws \Exception
     */
    public function syncData(string $destinationTable, array $ids = []): void
    {
        $connection = $this->getIndexer()->getConnection();
        $connection->beginTransaction();
        try {
            $sourceTable = $this->getIndexer()->getIdxTable();
            $columns = array_keys($connection->describeTable($sourceTable));
            $query = $connection->insertFromSelect(
                $connection->select()->from($sourceTable, $columns),
                $destinationTable,
                $columns,
                AdapterInterface::INSERT_ON_DUPLICATE
            );
            $connection->query($query);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
