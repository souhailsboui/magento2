<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Sales\Orders;

use Amasty\Reports\Model\ResourceModel\Filters\AddFromFilter;
use Amasty\Reports\Model\ResourceModel\Filters\AddOrderStatusFilter;
use Amasty\Reports\Model\ResourceModel\Filters\AddStoreFilter;
use Amasty\Reports\Model\ResourceModel\Filters\AddToFilter;
use Amasty\Reports\Model\ResourceModel\Filters\RequestFiltersProvider;
use Amasty\Reports\Model\Utilities\CreateUniqueHash;
use Amasty\Reports\Model\Utilities\Order\GlobalRateResolver;
use Amasty\Reports\Model\Utilities\TimeZoneExpressionModifier;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Psr\Log\LoggerInterface;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    /**
     * @var TimeZoneExpressionModifier
     */
    private $expressionModifier;

    /**
     * @var AddFromFilter
     */
    private $addFromFilter;

    /**
     * @var AddToFilter
     */
    private $addToFilter;

    /**
     * @var AddStoreFilter
     */
    private $addStoreFilter;

    /**
     * @var AddOrderStatusFilter
     */
    private $addStatusFilter;

    /**
     * @var RequestFiltersProvider
     */
    private $filtersProvider;

    /**
     * @var CreateUniqueHash
     */
    private $createUniqueHash;

    /**
     * @var GlobalRateResolver
     */
    private $globalRateResolver;

    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Snapshot $entitySnapshot,
        DbHelper $coreResourceHelper,
        AddFromFilter $addFromFilter,
        AddToFilter $addToFilter,
        AddStoreFilter $addStoreFilter,
        AddOrderStatusFilter $addStatusFilter,
        TimeZoneExpressionModifier $expressionModifier,
        RequestFiltersProvider $filtersProvider,
        CreateUniqueHash $createUniqueHash,
        GlobalRateResolver $globalRateResolver,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $connection,
            $resource
        );

        $this->expressionModifier = $expressionModifier;
        $this->addFromFilter = $addFromFilter;
        $this->addToFilter = $addToFilter;
        $this->addStoreFilter = $addStoreFilter;
        $this->addStatusFilter = $addStatusFilter;
        $this->filtersProvider = $filtersProvider;
        $this->createUniqueHash = $createUniqueHash;
        $this->globalRateResolver = $globalRateResolver;
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Sales\Orders\Grid\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $this->joinSalesOrderItem($collection);

        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns([
                'total_orders' => 'COUNT(main_table.entity_id)',
                'total_items' => 'ROUND(SUM(main_table.total_qty_ordered))',
                'subtotal' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('base_subtotal')
                ),
                'tax' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('base_tax_amount')
                ),
                'status' => 'main_table.status',
                'shipping' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('base_shipping_amount')
                ),
                'discounts' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('base_discount_amount')
                ),
                'total' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('base_grand_total')
                ),
                'invoiced' => sprintf(
                    'IFNULL(SUM(%s), 0)',
                    $this->globalRateResolver->resolvePriceColumn('base_total_invoiced')
                ),
                'refunded' => sprintf(
                    'IFNULL(SUM(%s), 0)',
                    $this->globalRateResolver->resolvePriceColumn('base_total_refunded')
                ),
                'entity_id' => 'CONCAT(main_table.entity_id,\'' . $this->createUniqueHash->execute() . '\')',
                'cost' => $this->getCostSelect(),
                'profit' => sprintf(
                    '(SUM(%s) + SUM(%s) - %s)',
                    $this->globalRateResolver->resolvePriceColumn('base_subtotal'),
                    $this->globalRateResolver->resolvePriceColumn('base_discount_amount'),
                    $this->getCostSelect()
                )
            ]);
    }

    private function getCostSelect(): string
    {
        return '(IF(SUM(sales_order_items.cost), SUM(sales_order_items.cost), 0))';
    }

    public function applyToolbarFilters(AbstractCollection $collection): void
    {
        $this->addFromFilter->execute($collection);
        $this->addToFilter->execute($collection);
        $this->addStoreFilter->execute($collection);
        $this->addGroupFilter($collection);
        $this->addStatusFilter->execute($collection);
    }

    private function joinSalesOrderItem(AbstractCollection $collection): void
    {
        $salesOrderItem = $this->getConnection()
            ->select()
            ->from(
                ['sales_order_item' => $this->getTable('sales_order_item')],
                [
                    'order_id' => 'order_id',
                    'cost' => sprintf(
                        'SUM(%s*qty_ordered)',
                        $this->globalRateResolver->resolvePriceColumn('base_cost')
                    )
                ]
            )
            ->where('product_type = "simple"')
            ->group('order_id');

        if ($this->globalRateResolver->isDefaultStore()) {
            $salesOrderItem->join(
                ['sales_order' => $this->getTable('sales_order')],
                'sales_order.entity_id = sales_order_item.order_id',
                []
            );
        }

        $collection->getSelect()->joinLeft(
            ['sales_order_items' => $salesOrderItem],
            'main_table.entity_id = sales_order_items.order_id'
        );
    }

    private function addGroupFilter(AbstractCollection $collection): void
    {
        $filters = $this->filtersProvider->execute();
        $group = isset($filters['type']) ? $filters['type'] : 'overview';
        switch ($group) {
            case 'overview':
                $expression = $this->expressionModifier->execute('main_table.created_at');
                $collection->getSelect()
                    ->columns([
                        'period' => "DATE($expression)",
                    ]);
                $collection->getSelect()->group("DATE($expression)");
                break;
            case 'status':
                $collection->getSelect()->columns([
                    'period' => "status",
                ]);
                $collection->getSelect()->group('status');
                break;
        }
    }
}
