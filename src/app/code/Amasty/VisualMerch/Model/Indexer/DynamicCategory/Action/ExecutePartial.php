<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Indexer\DynamicCategory\Action;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Indexer;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Indexer\SyncIndexedData;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Resources\TableWorker;
use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\CategoryIndex;

class ExecutePartial
{
    /**
     * @var TableWorker
     */
    private $tableWorker;

    /**
     * @var DoReindex
     */
    private $doReindex;

    /**
     * @var SyncIndexedData
     */
    private $syncIndexedData;

    /**
     * @var string
     */
    private $indexerType;

    public function __construct(
        TableWorker $tableWorker,
        DoReindex $doReindex,
        SyncIndexedData $syncIndexedData,
        string $indexerType
    ) {
        $this->tableWorker = $tableWorker;
        $this->doReindex = $doReindex;
        $this->syncIndexedData = $syncIndexedData;
        $this->indexerType = $indexerType;
    }

    public function execute(array $ids): void
    {
        $this->tableWorker->createTemporaryTable();

        $this->doReindex->execute(
            $this->indexerType === Indexer::CATEGORY_INDEXER_TYPE ? $ids : null,
            $this->indexerType === Indexer::PRODUCT_INDEXER_TYPE ? $ids : null
        );

        $fieldName = $this->indexerType === Indexer::CATEGORY_INDEXER_TYPE
            ? CategoryIndex::CATEGORY_ID_COLUMN
            : CategoryIndex::PRODUCT_ID_COLUMN;
        $this->tableWorker->syncDataPartial([
            sprintf('%s IN (?)', $fieldName) => $ids
        ]);

        $this->syncIndexedData->execute($this->indexerType === Indexer::CATEGORY_INDEXER_TYPE ? $ids : null);
    }
}
