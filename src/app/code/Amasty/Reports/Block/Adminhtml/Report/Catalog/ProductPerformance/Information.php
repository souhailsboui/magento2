<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Block\Adminhtml\Report\Catalog\ProductPerformance;

use Amasty\Reports\Helper\Data;
use Amasty\Reports\Model\Store as StoreResolver;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Information extends \Magento\Backend\Block\Template
{
    /**
     * @var \Amasty\Reports\Model\ResourceModel\Catalog\ProductPerformance\CollectionFactory
     */
    private $collection;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\Collection
     */
    private $reportCollection;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Directory\Model\PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreResolver
     */
    private $storeResolver;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Amasty\Reports\Model\ResourceModel\Catalog\ProductPerformance\CollectionFactory $collection,
        \Magento\Reports\Model\ResourceModel\Product\Collection $reportCollection,
        \Magento\Catalog\Helper\Image $imageHelper,
        DataObjectFactory $dataObjectFactory,
        \Magento\Directory\Model\PriceCurrency $priceCurrency,
        Data $helper,
        StoreResolver $storeResolver,
        array $data = []
    ) {
        $this->collection = $collection;
        $this->productRepository = $productRepository;
        $this->reportCollection = $reportCollection;
        $this->imageHelper = $imageHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->storeResolver = $storeResolver;
        parent::__construct($context, $data);
    }

    /**
     * @return DataObject
     */
    public function getProduct()
    {
        $filtersParams = $this->getRequest()->getParam('amreports');
        $result = $this->dataObjectFactory->create();
        if (isset($filtersParams['sku'])) {
            try {
                $product = $this->productRepository->get($filtersParams['sku']);
            } catch (NoSuchEntityException | LocalizedException $e) {
                $result->setError(__('Invalid SKU.'));
                return $result;
            }
            $this->prepareProductData($result, $product, $filtersParams);
        }

        return $result;
    }

    /**
     * @param $result
     * @param $product
     * @param $filtersParams
     */
    protected function prepareProductData($result, $product, $filtersParams)
    {
        $views = $this->getViewCount($filtersParams, $product);
        $orderInfo = $this->collection->create()->getOrderInfo($product->getId(), $product->getSku());

        $imageSrc = $this->imageHelper->init(
            $product,
            'thumbnail',
            [
                'type' => 'thumbnail',
                'width' => 75,
                'height' => 75
            ]
        )->getUrl();
        $result->setData(
            [
                'name' => $product->getName(),
                'price' => $this->priceCurrency->convertAndFormat(
                    $product->getPrice(),
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $this->storeResolver->getCurrentStoreId(),
                    $this->getCurrencyCode()
                ),
                'sku' => $product->getSku(),
                'views' => $views ?: 0,
                'qty' => $orderInfo->getQty(),
                'revenue' => $this->priceCurrency->convertAndFormat(
                    $orderInfo->getRevenue(),
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $this->storeResolver->getCurrentStoreId(),
                    $this->getCurrencyCode()
                ),
                'thumbnail' => $imageSrc,
            ]
        );
    }

    /**
     * @return mixed
     */
    private function getCurrencyCode()
    {
        return $this->helper->getDisplayCurrency();
    }

    /**
     * @param $productReport
     * @param $productId
     * @return int
     */
    private function getViewCount($filtersParams, $product)
    {
        $productId = $product->getId();
        $productReport = $this->reportCollection
            ->setProductAttributeSetId($product->getAttributeSetId())
            ->addViewsCount()
            ->addFieldToFilter('entity_id', $productId);
        $productReport->getSelect()
            ->where('DATE(logged_at) >= ?', $filtersParams['from'])
            ->where('DATE(logged_at) <= ?', $filtersParams['to']);

        if ($storeId = $this->storeResolver->getCurrentStoreId()) {
            $productReport->getSelect()->where('report_table_views.store_id = ?', $storeId);
        }

        $productReport = $productReport->getFirstItem();

        return $productReport->getViews();
    }
}
