<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model;

use Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection;
use Amasty\Reports\Model\Utilities\Order\GlobalRateResolver;
use Amasty\Reports\Model\Store as StoreResolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;

class Dashboard extends AbstractModel
{
    public const LAST_ORDER_COUNT = 10;
    public const BESTSELLERS_COUNT = 10;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceModel\Sales\Overview\Collection
     */
    private $salesCollection;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory
     */
    private $bestsellersCollectionFactory;

    /**
     * @var ResourceModel\Catalog\Bestsellers\Collection
     */
    private $bestsellersCollection;

    /**
     * @var GlobalRateResolver
     */
    private $globalRateResolver;

    /**
     * @var StoreResolver
     */
    private $storeResolver;

    public function __construct(
        Context $context,
        Registry $registry,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        Collection $salesCollection,
        \Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers\Collection $bestsellersCollection,
        \Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers\CollectionFactory $bestsellersCollectionFactory,
        GlobalRateResolver $globalRateResolver,
        StoreResolver $storeResolver,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->salesCollection = $salesCollection;
        $this->bestsellersCollectionFactory = $bestsellersCollectionFactory;
        $this->bestsellersCollection = $bestsellersCollection;
        $this->globalRateResolver = $globalRateResolver;
        $this->storeResolver = $storeResolver;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Reports\Model\ResourceModel\Report\Dashboard::class);
    }

    public function getConversionFunnel(string $from, string $to): array
    {
        return $this->getResource()->getFunnel($from, $to);
    }

    /**
     * @return \Magento\Reports\Model\ResourceModel\Order\Collection
     */
    public function getLastOrders()
    {
        $collection = $this->collectionFactory->create()
            ->addItemCountExpr()
            ->joinCustomerName('customer')
            ->orderByCreatedAt()
            ->setPageSize(self::LAST_ORDER_COUNT);
        $collection->addExpressionFieldToSelect(
            'base_grand_total',
            '({{base_grand_total}})',
            ['base_grand_total' => $this->globalRateResolver->resolvePriceColumn('base_grand_total')]
        );
        if ($this->storeResolver->getCurrentStoreId()) {
            $collection->addFieldToFilter('store_id', $this->storeResolver->getCurrentStoreId());
        }
        return $collection;
    }

    /**
     * @return mixed
     */
    public function getBestsellers()
    {
        $collection = $this->bestsellersCollection
            ->prepareCollection($this->bestsellersCollectionFactory->create())
            ->setPageSize(self::BESTSELLERS_COUNT);

        return $collection;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getSalesCollection()
    {
        $collection = $this->getCollection();
        $this->salesCollection->getDashboardCollection($collection);
        return $collection;
    }
}
