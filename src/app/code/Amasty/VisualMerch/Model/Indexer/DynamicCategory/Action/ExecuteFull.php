<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Indexer\DynamicCategory\Action;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Indexer\SyncIndexedData;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Resources\TableWorker;

class ExecuteFull
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

    public function __construct(TableWorker $tableWorker, DoReindex $doReindex, SyncIndexedData $syncIndexedData)
    {
        $this->tableWorker = $tableWorker;
        $this->doReindex = $doReindex;
        $this->syncIndexedData = $syncIndexedData;
    }

    public function execute(): void
    {
        $this->tableWorker->clearReplica();
        $this->tableWorker->createTemporaryTable();

        $this->doReindex->execute();

        $this->tableWorker->syncDataFull();
        $this->tableWorker->switchTables();

        $this->syncIndexedData->execute();
    }
}
