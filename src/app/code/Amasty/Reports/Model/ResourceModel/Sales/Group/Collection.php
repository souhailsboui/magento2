<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Sales\Group;

use Amasty\Reports\Model\Utilities\Order\GlobalRateResolver;
use Amasty\Reports\Traits\Filters;
use Magento\Framework\DB\Select;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
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
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        GlobalRateResolver $globalRateResolver,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
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
        $this->request = $request;
        $this->helper = $helper;
        $this->globalRateResolver = $globalRateResolver;
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Sales\Group\Grid\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinCustomerTable($collection)
    {
        $collection->getSelect()
            ->columns(['period' => 'cusgroup.customer_group_code'])
            ->join(
                ['cusgroup' => $this->getTable('customer_group')],
                'cusgroup.customer_group_id = main_table.customer_group_id'
            )
        ;
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()
            ->reset(Select::COLUMNS);
        $this->joinCustomerTable($collection);

        $collection->getSelect()
            ->columns([
                'total_orders' => 'COUNT(entity_id)',
                'total_items' => 'SUM(total_item_count)',
                'subtotal' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('base_subtotal')
                ),
                'tax' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('base_tax_amount')
                ),
                'status' => 'status',
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
                'entity_id' => 'CONCAT(entity_id,\''.$this->createUniqueEntity().'\')'
            ]);
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addCurrentStoreFilter($collection);
        $this->addGroupBy($collection);
        $this->addStatusFilter($collection);
    }

    /**
     * @param $collection
     */
    public function addGroupBy($collection)
    {
        $collection->getSelect()
            ->group("cusgroup.customer_group_id")
        ;
    }
}
