<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Helper;

use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as CatalogCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory as ProductSoldCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Wishlist;
use Mageplaza\AutoRelated\Model\Config\Source\AddProductTypes;
use Mageplaza\AutoRelated\Model\Config\Source\Direction;
use Mageplaza\AutoRelated\Model\Config\Source\ProductNotDisplayed;
use Mageplaza\AutoRelated\Model\Config\Source\Type;
use Mageplaza\AutoRelated\Model\ResourceModel\Report\Product\Collection as ProductViewCollection;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule\Collection;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule\CollectionFactory;
use Mageplaza\AutoRelated\Model\Rule as AutoRelatedRule;
use Mageplaza\AutoRelated\Model\RuleFactory as AutoRelatedRuleFactory;
use Zend_Db_Expr;

/**
 * Class Rule
 * @package Mageplaza\AutoRelated\Helper
 */
class Rule extends Data
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Configurable
     */
    protected $configurableType;

    /**
     * @var Grouped
     */
    protected $groupedType;

    /**
     * @var Selection
     */
    protected $bundleSelection;

    /**
     * @var ProductViewCollection
     */
    protected $productViewCollection;

    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var AutoRelatedRuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Stock
     */
    protected $stockHelper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Wishlist
     */
    protected $wishlist;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param SessionFactory $customerSession
     * @param DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     * @param Configurable $configurableType
     * @param Grouped $groupedType
     * @param Selection $bundleSelection
     * @param ProductViewCollection $productViewCollection
     * @param ProductCollection $productCollection
     * @param AutoRelatedRuleFactory $ruleFactory
     * @param Stock $stockHelper
     * @param ProductSoldCollection $productSoldCollection
     * @param CheckoutSession $checkoutSession
     * @param Wishlist $wishlist
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Registry $registry,
        SessionFactory $customerSession,
        DateTime $dateTime,
        CollectionFactory $collectionFactory,
        Configurable $configurableType,
        Grouped $groupedType,
        Selection $bundleSelection,
        ProductViewCollection $productViewCollection,
        ProductCollection $productCollection,
        AutoRelatedRuleFactory $ruleFactory,
        Stock $stockHelper,
        ProductSoldCollection $productSoldCollection,
        CheckoutSession $checkoutSession,
        Wishlist $wishlist
    ) {
        $this->collectionFactory     = $collectionFactory;
        $this->dateTime              = $dateTime;
        $this->configurableType      = $configurableType;
        $this->groupedType           = $groupedType;
        $this->bundleSelection       = $bundleSelection;
        $this->productViewCollection = $productViewCollection;
        $this->productCollection     = $productCollection;
        $this->ruleFactory           = $ruleFactory;
        $this->stockHelper           = $stockHelper;
        $this->checkoutSession       = $checkoutSession;
        $this->wishlist              = $wishlist;

        parent::__construct(
            $context,
            $objectManager,
            $storeManager,
            $registry,
            $productSoldCollection,
            $customerSession
        );
    }

    /**
     * @return bool
     */
    public function isEnableArpBlock()
    {
        if (!$this->getData('arp_enable')) {
            $enable = false;
            if ($this->isEnabled()) {
                switch ($this->_request->getFullActionName()) {
                    case 'catalog_product_view':
                        $this->setData('type', Type::TYPE_PAGE_PRODUCT);
                        $product = $this->registry->registry('current_product');
                        $this->setData('entity_id', $product ? $product->getId() : '');
                        $enable = $product ? !$product->getMpDisableAutoRelated() : false;
                        break;
                    case 'catalog_category_view':
                        $this->setData('type', Type::TYPE_PAGE_CATEGORY);
                        $category = $this->registry->registry('current_category');
                        $this->setData('entity_id', $category ? $category->getId() : '');
                        $enable = true;
                        break;
                    case 'checkout_cart_index':
                        $this->setData('type', Type::TYPE_PAGE_SHOPPING);
                        $enable = true;
                        break;
                    case 'onestepcheckout_index_index':
                        $this->setData('type', Type::TYPE_PAGE_OSC);
                        $enable = true;
                        break;
                    case 'checkout_onepage_success':
                        $this->setData('type', Type::TYPE_PAGE_CHECKOUT_SUCCESS);
                        $enable = true;
                        break;
                    case 'cms_page_view':
                    case 'cms_index_index':
                        $this->setData('type', Type::CMS_PAGE);
                        $enable = true;
                        break;
                }
            }

            $this->setData('arp_enable', $enable);
        }

        return $this->getData('arp_enable');
    }

    /**
     * @param string $mode
     *
     * @return array|null
     */
    public function getActiveRulesByMode($mode)
    {
        if (!$this->getData('rule_mode_' . $mode)) {
            $rules = [];
            foreach ($this->getActiveRules() as $rule) {
                if ($rule->getDisplayMode() === $mode) {
                    $rules[] = $rule;
                }
            }

            $this->setData('rule_mode_' . $mode, $rules);
        }

        return $this->getData('rule_mode_' . $mode);
    }

    /**
     * @return array
     */
    public function getProductRule()
    {
        $ruleId          = [];
        $ruleCollections = $this->collectionFactory->create();
        $ruleCollections->addActiveFilter($this->getCustomerGroup(), $this->getCurrentStore())
            ->addDateFilter($this->dateTime->date('Y-m-d'))
            ->setOrder('sort_order', 'asc');

        if ($this->getCurrentProduct()) {
            $product = $this->getCurrentProduct();
            foreach ($ruleCollections as $rule) {
                /** @var AutoRelatedRule $rule */
                if ($rule->getConditions()->validate($product) && $rule->getLocation() == 'product-tab') {
                    $ruleId[] = $rule->getRuleId();
                }
            }
        }

        return $ruleId;
    }

    /**
     * @return Collection
     */
    public function getActiveRules()
    {
        if (!$this->getData('active_rules')) {
            /** @var Collection $ruleCollections */
            $ruleCollections = $this->collectionFactory->create();
            $ruleCollections->addActiveFilter($this->getCustomerGroup(), $this->getCurrentStore())
                ->addDateFilter($this->dateTime->date('Y-m-d'))
                ->addTypeFilter($this->getData('type'))
                ->addLocationFilter(['nin' => ['custom', 'cms-page']]);

            $this->setData('active_rules', $ruleCollections);
        }

        return $this->getData('active_rules');
    }

    /**
     * Retrieve custom rules
     *
     * @return Collection
     */
    public function getCustomRules()
    {
        if (!$this->getData('custom_rules')) {
            /** @var Collection $ruleCollections */
            $ruleCollections = $this->collectionFactory->create();
            $ruleCollections->addActiveFilter($this->getCustomerGroup(), $this->getCurrentStore())
                ->addDateFilter($this->dateTime->date())
                ->addTypeFilter($this->getData('type'))
                ->addLocationFilter(['in' => ['custom', 'cms-page']]);

            $this->setData('custom_rules', $ruleCollections);
        }

        return $this->getData('custom_rules');
    }

    /**
     * @return CatalogCollection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductCollection()
    {
        $rule       = $this->getCurrentRule();
        $productIds = $rule->getApplyProductIds();

        if ($rule->getAddRucProduct() && !empty($this->addAdditionProducts())) {
            $productIds = array_unique(array_merge($productIds, $this->addAdditionProducts()));
        }

        if ($rule->getProductNotDisplayed() && count($this->removeProducts())) {
            $productIds = array_diff($productIds, $this->removeProducts());
        }

        $collection = $this->productCollection->create();
        $collection->addIdFilter($productIds)->addAttributeToSelect('*')
            ->setVisibility([
                Visibility::VISIBILITY_IN_CATALOG,
                Visibility::VISIBILITY_BOTH
            ])
            ->addStoreFilter()
            ->addAttributeToFilter('status', 1);

        if (!$rule->getDisplayOutOfStock()) {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }

        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite();

        if ($limit = $rule->getLimitNumber()) {
            $collection->getSelect()->limit($limit);
        }

        $collection    = $this->sortProduct($rule, $collection);
        return $this->productCollection->create()->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $collection->getColumnValues('entity_id')]);
    }

    /**
     * @return AutoRelatedRule
     */
    public function getCurrentRule()
    {
        $ruleId = $this->_request->getParam('rule_id');

        return $this->ruleFactory->create()->load($ruleId);
    }

    /**
     * @return array
     */
    public function addAdditionProducts($rule = null, $product = null)
    {
        if ($rule == null) {
            $rule = $this->getCurrentRule();
        }

        $productIds = [];

        if (!in_array($rule->getBlockType(), ['product', 'cart'])) {
            return $productIds;
        }

        if ($product == null) {
            if ($this->getCurrentProduct()) {
                $product = $this->getCurrentProduct();
            }
        }

        $addProductTypes = explode(',', $rule['add_ruc_product']);
        if (in_array(AddProductTypes::RELATED_PRODUCT, $addProductTypes, true) && $product) {
            $productIds = array_merge($productIds, $product->getRelatedProductIds());
        }
        if (in_array(AddProductTypes::UP_SELL_PRODUCT, $addProductTypes, true) && $product) {
            $productIds = array_merge($productIds, $product->getUpSellProductIds());
        }
        if (in_array(AddProductTypes::CROSS_SELL_PRODUCT, $addProductTypes, true) && $product) {
            $productIds = array_merge($productIds, $product->getCrossSellProductIds());
        }

        return $productIds;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function removeProducts()
    {
        $rule                = $this->getCurrentRule();
        $productIds          = [];
        $productNotDisplayed = explode(',', $rule['product_not_displayed']);
        $customerId          = $this->customerSession->create()->getCustomer()->getId();

        if (in_array(ProductNotDisplayed::IN_CART, $productNotDisplayed)) {
            $quoteSession    = $this->checkoutSession->create();
            $cartProductList = $quoteSession->getQuote()->getAllItems();
            foreach ($cartProductList as $item) {
                $productIds[] = $item->getProductId();
            }
        }

        if (in_array(ProductNotDisplayed::IN_WISHLIST, $productNotDisplayed) && $customerId) {
            $wishListItems = $this->wishlist->loadByCustomerId($customerId)->getItemCollection();
            if (count($wishListItems)) {
                foreach ($wishListItems as $item) {
                    $productIds[] = $item->getProduct()->getId();
                }
            }
        }

        return $productIds;
    }

    /**
     * @param AutoRelatedRule $rule
     * @param CatalogCollection $collection
     *
     * @return CatalogCollection
     */
    public function sortProduct(AutoRelatedRule $rule, CatalogCollection $collection)
    {
        switch ($rule->getSortOrderDirection()) {
            case Direction::BESTSELLER:
                $productIds = [];
                $collection->getSelect()->joinLeft(
                    ['soi' => $collection->getTable('sales_bestsellers_aggregated_yearly')],
                    'e.entity_id = soi.product_id',
                    ['qty_ordered' => 'SUM(soi.qty_ordered)']
                )
                    ->group('e.entity_id')
                    ->order('qty_ordered DESC');
                /** @var Product $product */
                foreach ($collection->getItems() as $product) {
                    if (in_array($product->getId(), $productIds, true)) {
                        continue;
                    }

                    if ($product->getData('visibility') != 1) {
                        $productIds[] = $product->getId();
                    }
                }

                $collection = $rule->getProductCollectionVisibility();
                $collection->getSelect()->where('e.entity_id IN (?)', $productIds);
                $collection->getSelect()->reset(Select::ORDER);
                $collection->getSelect()
                    ->order(new Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $productIds) . ')'));

                break;
            case Direction::PRICE_LOW:
                $collection->setVisibility([
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH
                ]);
                $collection->getSelect()->order('final_price ASC');
                break;
            case Direction::PRICE_HIGH:
                $collection->setVisibility([
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH
                ]);
                $collection->getSelect()->order('final_price DESC');
                break;
            case Direction::NEWEST:
                $collection->setVisibility([
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH
                ]);
                $collection->getSelect()->order('e.created_at DESC');
                break;
            case Direction::MOST_VIEWED:
                $allIds = $collection->setVisibility([
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH
                ])->getAllIds();

                $collection = $this->productViewCollection->addFieldToSelect('*')->addViewsCount()
                    ->addFieldToFilter('entity_id', ['in' => $allIds]);
                break;
            case Direction::PRODUCT_NAME_ASC:
                $collection->setVisibility([
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH
                ]);
                $allIds     = $collection->getColumnValues('entity_id');
                $collection = $this->productCollection->create()->addFieldToSelect('*');
                $collection->setOrder('name', 'asc')->addFieldToFilter('entity_id', ['in' => $allIds]);
                break;
            case Direction::PRODUCT_NAME_DESC:
                $collection->setVisibility([
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH
                ]);
                $allIds     = $collection->getColumnValues('entity_id');
                $collection = $this->productCollection->create()->addFieldToSelect('*');
                $collection->setOrder('name', 'desc')->addFieldToFilter('entity_id', ['in' => $allIds]);
                break;
            default:
                $collection->setVisibility([
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH
                ]);
                $collection->getSelect()->order('rand()');
                break;
        }

        return $collection;
    }

    /**
     * @param Product $product
     *
     * @return string|null
     */
    private function getFirstParentId($product)
    {
        $configurableProducts = $this->configurableType->getParentIdsByChild($product->getId());
        if (!empty($configurableProducts)) {
            return array_shift($configurableProducts);
        }

        $groupedProducts = $this->groupedType->getParentIdsByChild($product->getId());
        if (!empty($groupedProducts)) {
            return array_shift($groupedProducts);
        }

        $bundleProducts = $this->bundleSelection->getParentIdsByChild($product->getId());
        if (!empty($bundleProducts)) {
            return array_shift($bundleProducts);
        }

        return null;
    }
}
