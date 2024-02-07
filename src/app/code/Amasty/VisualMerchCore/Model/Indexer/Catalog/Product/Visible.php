<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\Indexer\Catalog\Product;

use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Visible\Action\Row;
use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Visible\Action\Rows;
use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Visible\Action\Full;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class Visible implements ActionInterface, MviewActionInterface
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

    public function __construct(
        Row $productEavIndexerRow,
        Rows $productEavIndexerRows,
        Full $productEavIndexerFull
    ) {
        $this->indexerRow = $productEavIndexerRow;
        $this->indexerRows = $productEavIndexerRows;
        $this->indexerFull = $productEavIndexerFull;
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
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeFull()
    {
        $this->indexerFull->execute();
    }

    /**
     * @param array $ids
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeList(array $ids)
    {
        $this->indexerRows->execute($ids);
    }

    /**
     * @param int $id
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeRow($id)
    {
        $this->indexerRow->execute((int)$id);
    }
}
