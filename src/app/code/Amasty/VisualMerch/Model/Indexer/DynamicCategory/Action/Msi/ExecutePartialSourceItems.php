<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Indexer\DynamicCategory\Action\Msi;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Action\DoReindex;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Action\ExecutePartial;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Indexer\SyncIndexedData;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Resources\TableWorker;
use Amasty\VisualMerch\Model\Product\Msi\ConvertSourceItemIds;

class ExecutePartialSourceItems extends ExecutePartial
{
    /**
     * @var ConvertSourceItemIds
     */
    private $convertSourceItemIds;

    public function __construct(
        TableWorker $tableWorker,
        DoReindex $doReindex,
        SyncIndexedData $syncIndexedData,
        string $indexerType,
        ConvertSourceItemIds $convertSourceItemIds
    ) {
        parent::__construct($tableWorker, $doReindex, $syncIndexedData, $indexerType);
        $this->convertSourceItemIds = $convertSourceItemIds;
    }

    public function execute(array $ids): void
    {
        parent::execute(
            $this->convertSourceItemIds->execute($ids)
        );
    }
}
