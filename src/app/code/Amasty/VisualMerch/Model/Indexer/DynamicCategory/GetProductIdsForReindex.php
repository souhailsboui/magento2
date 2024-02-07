<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Indexer\DynamicCategory;

use Amasty\VisualMerch\Model\Product\LoadRelationsByChild;

class GetProductIdsForReindex
{
    /**
     * @var LoadRelationsByChild
     */
    private $loadRelationsByChild;

    public function __construct(LoadRelationsByChild $loadRelationsByChild)
    {
        $this->loadRelationsByChild = $loadRelationsByChild;
    }

    public function execute(array $productIds): array
    {
        $parentIds = $this->loadRelationsByChild->execute($productIds);
        return array_unique(array_merge($parentIds, $productIds));
    }
}
