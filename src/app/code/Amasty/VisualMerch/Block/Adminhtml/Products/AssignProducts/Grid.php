<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Block\Adminhtml\Products\AssignProducts;

use \Magento\Backend\Block\Widget\Grid\Extended;

class Grid extends \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
{
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $visibility;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $status;

    /**
     * @var \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider
     */
    protected $dataProvider;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $dataProvider,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $productFactory, $coreRegistry, $data);
        $this->visibility = $visibility;
        $this->status = $status;
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setCategoryId($this->getCategoryId());
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('amasty_visual_merch/product/popup', ['_current' => true]);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    protected function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', $this->getRequest()->getParam('store_id', 0));
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return int
     */
    private function getCategoryId(): int
    {
        return (int)$this->getRequest()->getParam('id', 0);
    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'price'
        )->joinField(
            'position',
            'catalog_category_product',
            'position',
            'product_id=entity_id',
            'category_id=' . $this->getCategoryId(),
            'left'
        );
        $collection->joinAttribute(
            'status',
            'catalog_product/status',
            'entity_id',
            null,
            'inner',
            $this->getStore()->getId()
        );
        $collection->joinAttribute(
            'visibility',
            'catalog_product/visibility',
            'entity_id',
            null,
            'inner',
            $this->getStore()->getId()
        );
        $storeId = (int)$this->getRequest()->getParam('store_id', $this->getRequest()->getParam('store', 0));
        if ($storeId > 0) {
            $collection->addStoreFilter($storeId);
        }
        $this->setCollection($collection);

        if ($this->getCategory()->getProductsReadonly()) {
            $productIds = $this->getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        return Extended::_prepareCollection();
    }

    /**
     * @return array
     */
    private function getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            $this->dataProvider->initSession();
            $products = $this->dataProvider->getCategoryProductIds();
        }

        return $products;
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_category',
            [
                'type' => 'checkbox',
                'name' => 'in_category',
                'values' => $this->getSelectedProducts(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );
        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray()
            ]
        );

        return Extended::_prepareColumns();
    }
}
