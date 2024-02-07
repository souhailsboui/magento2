<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Eav;

use Amasty\VisualMerchCore\Model\ResourceModel\Product\Indexer\Eav\Adapter;
use Amasty\VisualMerchCore\Model\ResourceModel\Product\Indexer\Eav\AdapterFactory;
use Magento\Framework\Exception\LocalizedException;

class IndexAdapter
{
    /**
     * @var Adapter
     */
    private $indexer;

    public function __construct(AdapterFactory $adapterFactory)
    {
        $this->indexer = $adapterFactory->create();
    }

    /**
     * @param string $type
     * @return Adapter
     * @throws LocalizedException
     */
    public function getIndexer(): Adapter
    {
        return $this->indexer;
    }

    /**
     * @param array $ids
     * @param bool $onlyParents
     * @return array $ids
     */
    public function processRelations(array $ids, bool $onlyParents = false): array
    {
        $parentIds = $this->indexer->getRelationsByChild($ids);
        $parentIds = array_unique(array_merge($parentIds, $ids));
        $childIds = $onlyParents ? [] : $this->indexer->getRelationsByParent($parentIds);

        return array_unique(array_merge($childIds, $parentIds));
    }

    /**
     * @param array $ids
     * @throws \Exception
     * @return void
     */
    public function syncData(array $ids = []): void
    {
        $connection = $this->indexer->getConnection();
        $connection->beginTransaction();
        try {
            $destinationTable = $this->indexer->getMainTable();
            if (!empty($ids)) {
                $where = $connection->quoteInto('entity_id IN(?)', $ids);
                $connection->delete($destinationTable, $where);
            } else {
                $connection->delete($destinationTable);
            }

            $this->indexer->insertFromTable($this->indexer->getIdxTable(), $destinationTable);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
