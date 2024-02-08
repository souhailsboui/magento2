<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Visible\Action;

use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Visible\IndexAdapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\InputException;

class Row
{
    /**
     * @var IndexAdapter
     */
    private $indexAdapter;

    public function __construct(IndexAdapter $indexAdapter)
    {
        $this->indexAdapter = $indexAdapter;
    }

    /**
     * @param int|null $id
     * @return void
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute(?int $id = null): void
    {
        if (!isset($id) || empty($id)) {
            throw new InputException(__('We can\'t rebuild the index for an undefined product.'));
        }
        try {
            $this->indexAdapter->getIndexer()->reindexEntities([$id]);
            $this->indexAdapter->syncData([$id]);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
