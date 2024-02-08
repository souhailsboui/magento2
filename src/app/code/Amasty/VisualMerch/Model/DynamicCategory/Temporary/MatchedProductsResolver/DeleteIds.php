<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver;

use Amasty\VisualMerch\Model\Category\ResolveRootCategoryId;
use Amasty\VisualMerch\Model\DynamicCategory\Store\GetStoresForRootCategory;
use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\CategoryTemporary;
use Magento\Catalog\Api\Data\CategoryInterface;

class DeleteIds
{
    /**
     * @var CategoryTemporary
     */
    private $categoryTemporary;

    /**
     * @var GetHash
     */
    private $getHash;

    /**
     * @var ResolveRootCategoryId
     */
    private $resolveRootCategoryId;

    /**
     * @var GetStoresForRootCategory
     */
    private $getStoresForRootCategory;

    public function __construct(
        CategoryTemporary $categoryTemporary,
        GetHash $getHash,
        GetStoresForRootCategory $getStoresForRootCategory,
        ResolveRootCategoryId $resolveRootCategoryId
    ) {
        $this->categoryTemporary = $categoryTemporary;
        $this->getHash = $getHash;
        $this->resolveRootCategoryId = $resolveRootCategoryId;
        $this->getStoresForRootCategory = $getStoresForRootCategory;
    }

    public function execute(CategoryInterface $category, string $conditionsSerialized): void
    {
        $storeIds = $this->getStoresForRootCategory->execute($this->resolveRootCategoryId->execute($category));
        if (!$storeIds) {
            return;
        }

        $this->categoryTemporary->delete(
            $this->getHash->execute($storeIds, $conditionsSerialized),
            (int)$category->getId()
        );
    }
}
