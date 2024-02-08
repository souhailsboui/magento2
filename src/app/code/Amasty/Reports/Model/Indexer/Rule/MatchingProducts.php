<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\Indexer\Rule;

use Amasty\Reports\Api\Data\RuleInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Rule\Model\Condition\Sql\Builder;
use Magento\Store\Model\StoreManagerInterface;

class MatchingProducts
{
    public const ROW_ARG = 'row';

    public const STORE_ID_ARG = 'store_id';

    public const PRODUCT_ARG = 'product';

    public const RULE_ARG = 'rule';

    /**
     * @var array
     */
    private $matchedProducts = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Iterator
     */
    private $resourceIterator;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Builder
     */
    private $builder;

    public function __construct(
        StoreManagerInterface $storeManager,
        Iterator $resourceIterator,
        ProductFactory $productFactory,
        ProductCollectionFactory $productCollectionFactory,
        Builder $builder
    ) {
        $this->storeManager = $storeManager;
        $this->resourceIterator = $resourceIterator;
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->builder = $builder;
    }

    /**
     * Iterate product collection through rule conditions. Return product IDs in Store IDs
     *
     * @param RuleInterface $rule
     * @return array [
     *    (int) => [(int)] // Store IDs
     * ]// key = Product ID
     */
    public function resolveProductIdsByReportRule(RuleInterface $rule): array
    {
        $this->matchedProducts = [];
        $rule->setData('collected_attributes', []);
        if ($rule->getConditions() && !$rule->isConditionEmpty()) {
            foreach ($this->storeManager->getStores() as $store) {
                $this->collectProductsByConditions($rule, (int) $store->getId());
            }
        }

        $matchedProducts = $this->matchedProducts;
        $this->matchedProducts = [];

        return $matchedProducts;
    }

    private function collectProductsByConditions(RuleInterface $rule, int $storeId): void
    {
        /** @var ProductCollection $productCollection */
        $productCollection = $this->productCollectionFactory->create()
            ->setStoreId($storeId);

        if ($rule->getProductsFilter()) {
            $productCollection->addIdFilter($rule->getProductsFilter());
        }

        $conditions = $rule->getConditions();
        $conditions->collectValidatedAttributes($productCollection);

        $this->builder->attachConditionToCollection($productCollection, $conditions);

        $this->resourceIterator->walk(
            $productCollection->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
                self::RULE_ARG => $rule,
                self::PRODUCT_ARG => $this->productFactory->create(),
                self::STORE_ID_ARG => $storeId
            ]
        );
    }

    public function callbackValidateProduct(array $args): void
    {
        /** @var RuleInterface $product */
        $rule = $args[self::RULE_ARG];
        /** @var Product $product */
        $product = $args[self::PRODUCT_ARG];
        /** @var int $storeId */
        $storeId = $args[self::STORE_ID_ARG];

        $product->setData($args[self::ROW_ARG]);
        $product->setStoreId($storeId);

        if ($rule->getConditions()->validate($product)) {
            $this->matchedProducts[(int)$product->getId()][] = $storeId;
        }
    }
}
