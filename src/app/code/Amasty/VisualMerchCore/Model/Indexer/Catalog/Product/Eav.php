<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\Indexer\Catalog\Product;

use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Eav\Action\Row;
use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Eav\Action\Rows;
use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Eav\Action\Full;
use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Visible;

class Eav implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var Row
     */
    private $indexerRow;

    /**
     * @var Rows
     */
    private $indexerRows;

    /**
     * @var Full
     */
    private $indexerFull;

    /**
     * @var Visible
     */
    private $visibleIndexer;

    public function __construct(
        Row $productEavIndexerRow,
        Rows $productEavIndexerRows,
        Full $productEavIndexerFull,
        Visible $visibleIndexer
    ) {
        $this->indexerRow = $productEavIndexerRow;
        $this->indexerRows = $productEavIndexerRows;
        $this->indexerFull = $productEavIndexerFull;
        $this->visibleIndexer = $visibleIndexer;
    }

    /**
     * @param int[] $ids
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->indexerRows->execute($ids);
        $this->visibleIndexer->execute($ids);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeFull()
    {
        $this->indexerFull->execute();
        $this->visibleIndexer->executeFull();
    }

    /**
     * @param array $ids
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeList(array $ids)
    {
        $this->indexerRows->execute($ids);
        $this->visibleIndexer->executeList($ids);
    }

    /**
     * @param int $id
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeRow($id)
    {
        $this->indexerRow->execute((int) $id);
        $this->visibleIndexer->executeRow((int) $id);
    }
}
