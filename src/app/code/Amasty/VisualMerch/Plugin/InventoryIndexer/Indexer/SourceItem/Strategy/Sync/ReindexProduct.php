<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\InventoryIndexer\Indexer\SourceItem\Strategy\Sync;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\ProductProcessor;
use Amasty\VisualMerch\Model\Product\Msi\ConvertSourceItemIds;
use Magento\InventoryIndexer\Indexer\SourceItem\Strategy\Sync;

class ReindexProduct
{
    /**
     * @var ConvertSourceItemIds
     */
    private $convertSourceItemIds;

    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    public function __construct(ConvertSourceItemIds $convertSourceItemIds, ProductProcessor $productProcessor)
    {
        $this->convertSourceItemIds = $convertSourceItemIds;
        $this->productProcessor = $productProcessor;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(
        Sync $subject,
        $result,
        array $sourceItemIds
    ): void {
        $this->productProcessor->reindexList(
            $this->convertSourceItemIds->execute($sourceItemIds)
        );
    }
}
