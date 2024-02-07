<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Eav\Action;

use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Eav\IndexAdapter;
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
            $ids = $this->indexAdapter->processRelations([$id]);
            $this->indexAdapter->getIndexer()->reindexEntities($ids);
            $this->indexAdapter->syncData($ids);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
