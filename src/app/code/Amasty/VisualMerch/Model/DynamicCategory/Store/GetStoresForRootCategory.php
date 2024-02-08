<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Store;

class GetStoresForRootCategory
{
    /**
     * @var GetStores
     */
    private $getStores;

    public function __construct(GetStores $getStores)
    {
        $this->getStores = $getStores;
    }

    /**
     * @return int[]
     */
    public function execute(int $rootCategoryId): array
    {
        return $this->getStores->execute()[$rootCategoryId] ?? [];
    }
}
