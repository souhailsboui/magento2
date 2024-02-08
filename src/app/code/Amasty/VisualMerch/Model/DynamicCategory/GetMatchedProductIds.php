<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory;

use Amasty\VisualMerch\Model\Rule\Condition\Combine;
use Amasty\VisualMerch\Model\Rule\Condition\Optimization\ConditionsOptimizerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class GetMatchedProductIds
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConditionsOptimizerInterface
     */
    private $conditionsOptimizer;

    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        ConditionsOptimizerInterface $conditionsOptimizer
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->conditionsOptimizer = $conditionsOptimizer;
    }

    public function execute(Combine $conditions, int $storeId, ?array $productsFilter = null): array
    {
        if (count($conditions->getConditions()) === 0) {
            return [];
        }

        $prevStoreId = $this->storeManager->getStore()->getId();
        $this->storeManager->setCurrentStore($storeId);

        $this->conditionsOptimizer->optimize($conditions);

        $productCollection = $this->collectionFactory->create();
        $productCollection->setStoreId($storeId);
        if ($productsFilter !== null) {
            $productCollection->addIdFilter($productsFilter);
        }

        $conditions->collectValidatedAttributes($productCollection);
        $condition = $conditions->collectConditionSql();
        if (!empty($condition)) {
            $productCollection->getSelect()->where($condition);
        }
        $productCollection->getSelect()->group('e.entity_id');

        $this->storeManager->setCurrentStore($prevStoreId);

        return $productCollection->getAllIds();
    }
}
