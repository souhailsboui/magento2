<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Visible;

use Amasty\VisualMerchCore\Model\ResourceModel\Product\Indexer\Visible\Adapter;
use Amasty\VisualMerchCore\Model\ResourceModel\Product\Indexer\Visible\AdapterFactory;
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
                $where = $connection->quoteInto(sprintf('%s IN(?)', Adapter::ID_FIELD_NAME), $ids);
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
