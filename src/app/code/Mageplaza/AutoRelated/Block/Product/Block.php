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

namespace Mageplaza\AutoRelated\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\SessionFactory as QuoteSessionFactory;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data as UrlData;
use Magento\Framework\View\LayoutFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory as ProductSoldCollection;
use Magento\Widget\Block\BlockInterface;
use Magento\Wishlist\Helper\Data as WishlistData;
use Magento\Wishlist\Model\Wishlist;
use Mageplaza\AutoRelated\Helper\Rule;
use Mageplaza\AutoRelated\Model\Config\Source\ProductNotDisplayed;
use Mageplaza\AutoRelated\Model\Config\Source\Type;
use Mageplaza\AutoRelated\Model\Rule as ModelRule;
use Mageplaza\AutoRelated\Model\RuleFactory;
use Zend_Db_Select_Exception;

/**
 * Class Block
 * @package Mageplaza\AutoRelated\Block\Product
 */
class Block extends AbstractProduct implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AutoRelated::product/block.phtml';

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ModelRule
     */
    protected $rule;

    /**
     * @var array
     */
    protected $displayTypes;

    /**
     * @var UrlData
     */
    protected $urlHelper;

    /**
     * @var
     */
    protected $rendererListBlock;

    /**
     * @var Stock
     */
    protected $stockHelper;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var QuoteSessionFactory
     */
    protected $quoteSessionFactory;

    /**
     * @var Wishlist
     */
    protected $wishlist;

    /**
     * @var WishlistData
     */
    protected $wishlistHelperData;

    /**
     * @var Compare
     */
    protected $catalogHelperCompare;

    /**
     * @var Rule
     */
    protected Rule $helper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var ProductSoldCollection
     */
    private ProductSoldCollection $productSoldCollection;

    /**
     * Block constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Wishlist $wishlist
     * @param QuoteSessionFactory $quoteSessionFactory
     * @param Rule $helper
     * @param UrlData $urlHelper
     * @param WishlistData $wishlistHelperData
     * @param Compare $catalogHelperCompare
     * @param Stock $stockHelper
     * @param RuleFactory $ruleFactory
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param ProductSoldCollection $productSoldCollection
     * @param LayoutFactory|null $layoutFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Wishlist $wishlist,
        QuoteSessionFactory $quoteSessionFactory,
        Rule $helper,
        UrlData $urlHelper,
        WishlistData $wishlistHelperData,
        Compare $catalogHelperCompare,
        Stock $stockHelper,
        RuleFactory $ruleFactory,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ProductSoldCollection $productSoldCollection,
        LayoutFactory $layoutFactory = null,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->urlHelper                = $urlHelper;
        $this->stockHelper              = $stockHelper;
        $this->layoutFactory            = $layoutFactory ?: ObjectManager::getInstance()->get(LayoutFactory::class);
        $this->ruleFactory              = $ruleFactory;
        $this->customerSession          = $customerSession;
        $this->quoteSessionFactory      = $quoteSessionFactory;
        $this->wishlist                 = $wishlist;
        $this->wishlistHelperData       = $wishlistHelperData;
        $this->catalogHelperCompare     = $catalogHelperCompare;
        $this->helper                   = $helper;
        $this->checkoutSession          = $checkoutSession;
        $this->productSoldCollection    = $productSoldCollection;

        parent::__construct($context, $data);
    }

    /**
     * @param ModelRule $rule
     *
     * @return $this
     */
    public function setRule($rule)
    {
        $this->rule = $rule;

        $location = $rule->getLocation();
        if ($location === 'left-popup-content' || $location === 'right-popup-content') {
            $this->setTemplate('Mageplaza_AutoRelated::product/block-floating.phtml');
        }

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getLocationBlock()
    {
        return $this->rule->getLocation();
    }

    /**
     * Get heading label
     *
     * @return string
     */
    public function getTitleBlock()
    {
        return $this->rule->getBlockName();
    }

    /**
     * @return string
     */
    public function getJsData()
    {
        return Rule::jsonEncode([
            'type'                    => $this->isSliderType() ? 'slider' : 'grid',
            'rule_id'                 => $this->rule->getId(),
            'parent_id'               => $this->rule->getData('parent_id'),
            'location'                => $this->rule->getData('location'),
            'number_product_slider'   => $this->rule->getData('number_product_slider') ?: 5,
            'number_product_scrolled' => $this->rule->getData('number_product_scrolled') ?: 2,
            'mode'                    => $this->rule->getData('display_mode'),
            'slider_config'           => $this->getSliderConfig()
        ]);
    }

    /**
     * Get layout config
     *
     * @return int
     */
    public function isSliderType()
    {
        return !$this->rule->getProductLayout();
    }

    /**
     * @return array|mixed
     */
    public function getSliderConfig()
    {
        return $this->helper->unserialize($this->rule->getData('slider_config'));
    }

    /**
     * @return mixed
     */
    public function showSeeAllUrl()
    {
        return $this->rule->getShowSeeAll();
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function canShow($type)
    {
        if ($this->displayTypes === null) {
            $this->displayTypes = $this->rule->getDisplayAdditional() ? explode(
                ',',
                $this->rule->getDisplayAdditional()
            ) : [];
        }

        return in_array($type, $this->displayTypes);
    }

    /**
     * @param Product $product
     *
     * @return int
     * @throws NoSuchEntityException
     * @throws Zend_Db_Select_Exception
     */
    public function getSoldQtyProduct($product)
    {
        $qty         = 0;
        $productType = $product->getTypeId();
        $storeId     = $this->_storeManager->getStore()->getId();

        if ($productType == 'configurable') {
            $children = $product->getTypeInstance()->getUsedProducts($product);

            $qty = $this->helper->getSoldQty($children, $storeId);
        } else {
            if ($productType == 'grouped') {
                $children = $product->getTypeInstance()->getAssociatedProducts($product);
                $qty      = $this->helper->getSoldQty($children, $storeId);
            } else {
                $productSoldCollection = $this->productSoldCollection->create()->addOrderedQty()
                    ->addAttributeToFilter('product_id', $product->getId());
                $productSoldCollection->getSelect()->where('order_items.store_id = ?', $storeId);
                foreach ($productSoldCollection as $productSold) {
                    $qty += $productSold->getData('ordered_qty');
                }
            }
        }

        return $qty;
    }

    /**
     * @return array|Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductCollection()
    {
        $rule = $this->rule;
        if (!$rule || !$rule->getId()) {
            return [];
        }

        $productIds = $rule->getApplyProductIds();
        if (empty($productIds)) {
            return [];
        }
        if ($rule->getAddRucProduct()) {
            if ($rule->getBlockType() == 'product' && !empty($this->helper->addAdditionProducts($this->rule))) {
                $productIds = array_unique(array_merge($productIds, $this->helper->addAdditionProducts($this->rule)));
            }
            if ($rule->getBlockType() == 'cart') {
                $currentQuote    = $this->checkoutSession->getQuote();
                $items           = $currentQuote->getAllVisibleItems();
                $productIdsMerge = [$productIds];

                foreach ($items as $item) {
                    if ($item->getParentItem()) {
                        continue;
                    }

                    if ($item->getHasChildren()) {
                        /** @var Item $child */
                        foreach ($item->getChildren() as $child) {
                            $additionalProducts = $this->helper->addAdditionProducts($this->rule, $child->getProduct());
                            $productIdsMerge[]  = $additionalProducts;
                        }
                    } elseif ($item instanceof Item) {
                        $additionalProducts = $this->helper->addAdditionProducts($this->rule, $item->getProduct());
                        $productIdsMerge[]  = $additionalProducts;
                    }
                }
                $productIds = array_merge([], ...$productIdsMerge);
            }
        }
        if ($this->rule->getProductNotDisplayed() && !empty($this->removeProducts())) {
            $productIds = array_diff($productIds, $this->removeProducts());
        }
        if (empty($productIds)) {
            return [];
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addIdFilter($productIds)
            ->setVisibility([
                Visibility::VISIBILITY_IN_CATALOG,
                Visibility::VISIBILITY_BOTH
            ])
            ->addStoreFilter()
            ->addAttributeToFilter('status', 1);

        $collection = $this->_addProductAttributesAndPrices($collection);

        if ($rule->getDisplayOutOfStock()) {
            $collection->setFlag('has_stock_status_filter', true);
            $this->stockHelper->addStockStatusToProducts($collection);
        } else {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }

        $newCollection = $this->productCollectionFactory->create()->addIdFilter($collection->getAllIds());
        $newCollection = $this->_addProductAttributesAndPrices($newCollection);

        if ($limit = $rule->getLimitNumber()) {
            $newCollection->getSelect()->limit($limit);
        }

        return $this->helper->sortProduct($rule, $newCollection);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function removeProducts()
    {
        $productIds          = [];
        $productNotDisplayed = explode(',', $this->rule['product_not_displayed']);
        $customer            = $this->customerSession->create();
        $customerId          = $customer->getCustomer()->getId();

        if (in_array(ProductNotDisplayed::IN_CART, $productNotDisplayed)) {
            /** @var  CheckoutSession $quoteSession */
            $quoteSession    = $this->quoteSessionFactory->create();
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
     * Get post parameters
     *
     * @param Product $product
     *
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product, ['_escape' => false]);

        return [
            'action' => $url,
            'data'   => [
                'product'                               => (int) $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * @return int
     */
    public function getPageColumnLayout()
    {
        return $this->rule->getPageColumnLayout() ?: 4;
    }

    /**
     * @return mixed
     */
    public function getRuleToken()
    {
        if ($parentId = $this->rule->getParentId()) {
            return $this->ruleFactory->create()->load($parentId)->getToken();
        }

        return $this->rule->getToken();
    }

    /**
     * @return WishlistData
     */
    public function getWishlistHelperData()
    {
        return $this->wishlistHelperData;
    }

    /**
     * @return Compare
     */
    public function getCatalogHelperCompare()
    {
        return $this->catalogHelperCompare;
    }

    /**
     * @return string
     */
    public function getSeeAllUrl()
    {
        $ruleId    = $this->getRuleId();
        $blockType = $this->rule->getBlockType();

        $urlParams = ['rule_id' => $ruleId];

        if ($blockType == Type::TYPE_PAGE_PRODUCT && $this->helper->getCurrentProduct()) {
            $urlParams['product_id'] = $this->helper->getCurrentProduct()->getId();
        }

        if ($blockType == Type::TYPE_PAGE_CATEGORY && $this->helper->getCurrentCategory()) {
            $urlParams['category_id'] = $this->helper->getCurrentCategory()->getId();
        }

        return $this->getUrl('autorelated/products/view', $urlParams);
    }

    /**
     * @return mixed
     */
    public function getRuleId()
    {
        return $this->rule->getId();
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    protected function getDetailsRendererList()
    {
        if (empty($this->rendererListBlock)) {
            $layout = $this->layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('catalog_widget_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();

            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.details.renderers');
        }

        return $this->rendererListBlock;
    }

    public function initRule()
    {
        if (!$this->rule) {
            $ruleId     = $this->_request->getPost('id');
            $this->rule = $this->ruleFactory->create()->load($ruleId);
        }
    }
}
