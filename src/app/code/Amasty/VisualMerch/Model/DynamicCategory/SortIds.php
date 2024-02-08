<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory;

use Amasty\VisualMerch\Model\Product\Sorting;
use Amasty\VisualMerch\Model\ResourceModel\Product as StaticPositionResource;
use Amasty\VisualMerch\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * Sort product ids with sorting method and static positions.
 */
class SortIds
{
    /**
     * @var StaticPositionResource
     */
    private $staticPositionResource;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Sorting
     */
    private $sorting;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        Sorting $sorting,
        StaticPositionResource $staticPositionResource
    ) {
        $this->staticPositionResource = $staticPositionResource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sorting = $sorting;
    }

    public function execute(int $categoryId, array $productIds, int $storeId, int $sortMethod): array
    {
        if (empty($productIds)) {
            return $productIds;
        }

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->setStoreId($storeId);
        $productCollection->addIdFilter($productIds);
        $this->sorting->applySorting($productCollection, $storeId, $sortMethod);
        $productIds = $productCollection->getProductIds(false);

        $sorted = $this->preparePositionDataForSort($categoryId, $productIds);
        $productIds = array_diff($productIds, $sorted);
        $itemsCount = count($productIds) + count($sorted);
        $idx = 0;
        while ($idx < $itemsCount) {
            if (!isset($sorted[$idx]) && current($productIds)) {
                $sorted[$idx] = current($productIds);
                next($productIds);
            }
            $idx++;
        }
        ksort($sorted, SORT_NUMERIC);

        return $sorted;
    }

    private function preparePositionDataForSort(int $categoryId, array $productIds): array
    {
        $positionData = $this->staticPositionResource->getPinnedIds($categoryId, $productIds);
        $positionData = array_intersect(array_flip($positionData), $productIds);
        $maxPosition = count($productIds) - 1;
        foreach ($positionData as $position => $productId) {
            if ($position > $maxPosition) {
                $positionData[$maxPosition] = $productId;
                $maxPosition--;
            }
        }

        return $positionData;
    }
}
