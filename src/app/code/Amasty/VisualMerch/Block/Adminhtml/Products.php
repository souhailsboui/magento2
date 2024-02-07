<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Block\Adminhtml;

use Amasty\Base\Model\Serializer;
use Magento\Framework\App\ObjectManager;

class Products extends \Magento\Backend\Block\Template
{
    /**
     * @var Products\Listing
     */
    private $listingBlock;

    /**
     * @var \Amasty\VisualMerch\Block\Adminhtml\Widget\Select\SortOrder
     */
    private $sortOrderBlock;

    /**
     * @var \Magento\Backend\Block\Widget\Button
     */
    private $sortOrderButtonBlock;

    /**
     * @var \Magento\Backend\Block\Widget\Button
     */
    private $addProductsButtonBlock;

    /**
     * @var \Amasty\VisualMerch\Block\Adminhtml\Widget\Input\Search
     */
    private $searchBlock;

    /**
     * @var \Magento\Backend\Block\Widget\Button
     */
    private $searchButtonBlock;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider
     */
    private $dataProvider;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $dataProvider,
        array $data = [],
        Serializer $serializer = null // TODO move to not optional
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->dataProvider = $dataProvider;
        $this->setTemplate('Amasty_VisualMerchUi::product/container.phtml');
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(Serializer::class);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListingBlock()
    {
        if (null === $this->listingBlock) {
            $this->listingBlock = $this->getLayout()->createBlock(
                \Amasty\VisualMerch\Block\Adminhtml\Products\Listing::class,
                'product.listing'
            );
        }
        return $this->listingBlock;
    }

    /**
     * @return Products\Listing|\Magento\Framework\View\Element\BlockInterface
     */
    public function getSortOrderBlock()
    {
        if (null === $this->sortOrderBlock) {
            $this->sortOrderBlock = $this->getLayout()->createBlock(
                \Amasty\VisualMerch\Block\Adminhtml\Widget\Select\SortOrder::class,
                'sort_order'
            );
            $this->sortOrderBlock->setLabel(__('Sort Order'))
                ->setClass('sort_order');
        }
        return $this->sortOrderBlock;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getSortOrderButtonBlock()
    {
        if (!$this->sortOrderButtonBlock) {
            $this->sortOrderButtonBlock = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class,
                'sort_order_button'
            );
            $this->sortOrderButtonBlock->setId('am-products-sort')
                ->setLabel(__('Sort'))
                ->setClass('secondary sort-products');

        }

        return $this->sortOrderButtonBlock;
    }

    /**
     * @return Products\Listing|\Magento\Framework\View\Element\BlockInterface
     */
    public function getSearchBlock()
    {
        if (null === $this->searchBlock) {
            $this->searchBlock = $this->getLayout()->createBlock(
                \Amasty\VisualMerch\Block\Adminhtml\Widget\Input\Search::class,
                'search'
            );
            $this->searchBlock->setId('am-products-search')
                ->setLabel(__('Search by SKU or name'))
                ->setName('search');
        }
        return $this->searchBlock;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getSearchButtonBlock()
    {
        if (!$this->searchButtonBlock) {
            $this->searchButtonBlock = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class,
                'search_button'
            );
            $this->searchButtonBlock->setId('am-products-search-button')
                ->setLabel(__('Search'))
                ->setClass('secondary sort-products');
        }

        return $this->searchButtonBlock;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getAddProductsButtonBlock()
    {
        if (!$this->addProductsButtonBlock) {
            $this->addProductsButtonBlock = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class,
                'add_products_button'
            );
            $this->addProductsButtonBlock->setId('am-add-products-button')
                ->setLabel(__('Add or Remove Products'))
                ->setClass('secondary sort-products');
            if ($this->getDispayMode()) {
                $this->addProductsButtonBlock->setStyle('display: none;');
            }
        }

        return $this->addProductsButtonBlock;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Page Products');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Page Products');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return string
     */
    public function getPositionDataJson()
    {
        return $this->serializer->serialize([]);
    }

    /**
     * @return string
     */
    public function getAssignProductsUrl()
    {
        return $this->prepareUrl('amasty_visual_merch/product/assign');
    }

    /**
     * @return string
     */
    public function getSavePositionsUrl()
    {
        return $this->prepareUrl('amasty_visual_merch/product/save');
    }

    /**
     * @return string
     */
    public function getSearchProductsUrl()
    {
        return $this->prepareUrl('amasty_visual_merch/product/search');
    }

    /**
     * @return string
     */
    public function getAddProductsUrl()
    {
        return $this->prepareUrl('amasty_visual_merch/product/add');
    }

    /**
     * @return string
     */
    public function getRemoveProductUrl()
    {
        return $this->prepareUrl('amasty_visual_merch/product/remove');
    }

    /**
     * @param $route
     * @return string
     */
    private function prepareUrl($route)
    {
        $storeId = (int)$this->_request->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $params = ['store_id' => $storeId];
        if ($category = $this->registry->registry('current_category')) {
            $params['entity_id'] = $category->getId();
        }
        return $this->getUrl($route, $params);
    }

    /**
     * @return bool
     */
    public function getDispayMode()
    {
        return $this->dataProvider->isDynamicMode();
    }
}
