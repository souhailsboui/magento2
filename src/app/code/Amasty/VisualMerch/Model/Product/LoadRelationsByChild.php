<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

class LoadRelationsByChild
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(ResourceConnection $resourceConnection, MetadataPool $metadataPool)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    public function execute(array $childIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)
            ->getLinkField();
        $select = $connection->select()->from(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'entity_id'
        )->join(
            ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
            'relation.parent_id = cpe.' . $linkField,
            []
        )->where('child_id IN(?)', $childIds);

        return $connection->fetchCol($select);
    }
}
