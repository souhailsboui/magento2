<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Catalog\ProductPerformance;

use Amasty\Reports\Model\Utilities\Order\GlobalRateResolver;
use Amasty\Reports\Traits\Filters;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\ColumnValueExpressionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection
{
    use Filters;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    protected $helper;

    /**
     * @var \Amasty\Reports\Model\ResourceModel\RuleIndex
     */
    protected $ruleIndex;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $productIdRow;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ColumnValueExpressionFactory
     */
    private $columnValueExpressionFactory;

    /**
     * @var GlobalRateResolver
     */
    private $globalRateResolver;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\Reports\Helper\Data $helper,
        ProductMetadataInterface $productMetadata,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        GlobalRateResolver $globalRateResolver,
        ColumnValueExpressionFactory $columnValueExpressionFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
        $this->request = $request;
        $this->helper = $helper;
        $this->productIdRow = $productMetadata->getEdition() != 'Community' ? 'row_id' : 'entity_id';
        $this->productRepository = $productRepository;
        $this->columnValueExpressionFactory = $columnValueExpressionFactory;
        $this->globalRateResolver = $globalRateResolver;
    }

    /**
     * @param $productId
     * @param $sku
     *
     * @return \Magento\Framework\DataObject
     */
    public function getOrderInfo($productId, $sku)
    {
        $this->addFromFilter($this);
        $this->addToFilter($this);
        $this->addCurrentStoreFilter($this);

        if ($this->globalRateResolver->isDefaultStore()) {
            $this->getSelect()->join(
                ['so' => $this->getTable('sales_order')],
                'main_table.order_id = so.entity_id',
                []
            );
        }

        $this->getSelect()
            ->reset(Select::COLUMNS)
            ->columns([
                'qty' => 'IFNULL(FLOOR(SUM(main_table.qty_ordered)), 0)',
                'revenue' => sprintf(
                    'IFNULL(SUM(%s), 0)',
                    $this->globalRateResolver->resolvePriceColumn('main_table.base_row_total')
                ),
            ])
            ->join(
                ['products' => $this->getTable('catalog_product_entity')],
                'find_in_set(products.sku, main_table.sku) > 0',
                []
            )
            ->where(
                sprintf(
                    '(main_table.parent_item_id IS NULL AND main_table.product_id = %s)'
                    . ' OR (main_table.parent_item_id IS NULL AND main_table.sku = %s)',
                    $this->getConnection()->quote($productId),
                    $this->getConnection()->quote($sku)
                )
            )
            ->limit(1);

        return $this->getLastItem();
    }

    /**
     * @param $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param AbstractCollection $collection
     */
    public function applyBaseFilters($collection)
    {
        $filters = $this->getRequestParams();
        $sku = isset($filters['sku']) ? $filters['sku'] : 0;
        $this->joinCustomerTable($collection);

        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns([
                'name' => 'CONCAT(sales_order.customer_firstname, " ", sales_order.customer_lastname)',
                'email' => 'sales_order.customer_email',
                'group' => 'IF(sales_order.customer_id IS NULL, "'
                    . __('NOT LOGGED IN') . '", customer_group.customer_group_code)',
                'order' => 'sales_order.increment_id',
                'date' => 'sales_order.created_at',
                'qty' => 'ROUND(main_table.qty_ordered)',
                'revenue' => $this->columnValueExpressionFactory->create([
                    'expression' => $this->globalRateResolver->resolvePriceColumn('main_table.base_row_total')
                ])
            ])
            ->where(
                sprintf(
                    '(main_table.parent_item_id IS NULL AND main_table.product_id = %s)'
                    . ' OR (main_table.parent_item_id IS NULL AND main_table.sku like %s)',
                    $this->getConnection()->quote($this->getProductId($sku)),
                    $this->getConnection()->quote($sku)
                )
            );
    }

    /**
     * @param $collection
     */
    private function joinCustomerTable($collection)
    {
        $collection->getSelect()
            ->join(
                ['sales_order' => $this->getTable('sales_order')],
                'main_table.order_id = sales_order.entity_id'
            )
            ->joinLeft(
                ['customer' => $this->getTable('customer_entity')],
                'customer.entity_id = sales_order.customer_id'
            )
            ->joinLeft(
                ['customer_group' => $this->getTable('customer_group')],
                'customer.group_id = customer_group.customer_group_id'
            );
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addCurrentStoreFilter($collection);
        $this->addStatusFilter($collection);
    }

    /**
     * @param string $sku
     *
     * @return int|null
     */
    private function getProductId($sku)
    {
        try {
            $productId = $this->productRepository->get($sku)->getId();
        } catch (NoSuchEntityException | LocalizedException $entityException) {
            $productId = null;
        }

        return $productId;
    }
}
