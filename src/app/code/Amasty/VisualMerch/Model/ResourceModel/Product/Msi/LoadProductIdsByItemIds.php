<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\ResourceModel\Product\Msi;

use Exception;
use Magento\Framework\App\ResourceConnection;

class LoadProductIdsByItemIds
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @throws Exception
     */
    public function execute(array $sourceItemIds): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            ['cpe.entity_id']
        )->join(
            ['isi' => $this->resourceConnection->getTableName('inventory_source_item')],
            'cpe.sku = isi.sku',
            []
        )->where(
            'isi.source_item_id IN (?)',
            $sourceItemIds
        );

        return (array) $this->resourceConnection->getConnection()->fetchCol($select);
    }
}
