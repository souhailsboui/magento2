<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Temporary;

use Amasty\VisualMerch\Model\Category\ResolveRootCategoryId;
use Amasty\VisualMerch\Model\DynamicCategory\Store\GetStoresForRootCategory;
use Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver\GetHash;
use Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver\LoadIds;
use Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver\ResolveIds;
use Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver\SaveIds;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Store\Model\Store;

class MatchedProductsResolver
{
    /**
     * @var GetHash
     */
    private $getHash;

    /**
     * @var LoadIds
     */
    private $loadIds;

    /**
     * @var ResolveIds
     */
    private $resolveIds;

    /**
     * @var SaveIds
     */
    private $saveIds;

    /**
     * @var ResolveRootCategoryId
     */
    private $resolveRootCategoryId;

    /**
     * @var GetStoresForRootCategory
     */
    private $getStoresForRootCategory;

    public function __construct(
        GetHash $getHash,
        LoadIds $loadIds,
        ResolveIds $resolveIds,
        SaveIds $saveIds,
        ResolveRootCategoryId $resolveRootCategoryId,
        GetStoresForRootCategory $getStoresForRootCategory
    ) {
        $this->getHash = $getHash;
        $this->loadIds = $loadIds;
        $this->resolveIds = $resolveIds;
        $this->saveIds = $saveIds;
        $this->resolveRootCategoryId = $resolveRootCategoryId;
        $this->getStoresForRootCategory = $getStoresForRootCategory;
    }

    public function execute(CategoryInterface $category, ?string $conditionsSerialized = null): ?array
    {
        $storeIds = $this->getStoresForRootCategory->execute($this->resolveRootCategoryId->execute($category));
        if (empty($storeIds)) {
            return [];
        }

        $conditionsSerialized = $conditionsSerialized ?? $category->getData('amasty_dynamic_conditions');
        $hash = $this->getHash->execute($storeIds, $conditionsSerialized);
        $matchedProductIds = $this->loadIds->execute($hash, (int)$category->getId());
        if ($matchedProductIds === null) {
            $matchedProductIds = [];
            foreach ($storeIds as $storeId) {
                array_push(
                    $matchedProductIds,
                    ...$this->resolveIds->execute($storeId, $conditionsSerialized)
                );
            }
            $this->saveIds->execute($hash, (int)$category->getId(), array_unique($matchedProductIds));
        }

        return $matchedProductIds;
    }
}
