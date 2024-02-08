<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Sales\State;

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
     * @param \Amasty\Reports\Model\ResourceModel\Sales\State\Grid\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinAddressTable($collection)
    {
        $collection->getSelect()
            ->joinLeft(
                ['sales_order_address' => $this->getTable('sales_order_address')],
                'sales_order_address.parent_id = main_table.entity_id'
            )
            ->where('address_type = "billing"')
        ;
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()
            ->reset(Select::COLUMNS);
        $this->joinAddressTable($collection);
        $period = $this->getFlag('force_sorting')
            ? 'IF(sales_order_address.region IS NOT NULL, sales_order_address.region, sales_order_address.country_id)'
            : 'sales_order_address.region';

        $collection->getSelect()->columns([
            'period' => $period,
            'country_id' => 'sales_order_address.country_id',
            'total_orders' => 'COUNT(main_table.entity_id)',
            'total_items' => 'SUM(main_table.total_item_count)',
            'subtotal' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_subtotal')
            ),
            'tax' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_tax_amount')
            ),
            'status' => 'main_table.status',
            'shipping' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_shipping_amount')
            ),
            'discounts' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_discount_amount')
            ),
            'total' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_grand_total')
            ),
            'invoiced' => sprintf(
                'IFNULL(SUM(%s), 0)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_total_invoiced')
            ),
            'refunded' => sprintf(
                'IFNULL(SUM(%s), 0)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_total_refunded')
            ),
            'revenue' => sprintf(
                '(IFNULL(SUM(%s), 0) - IFNULL(SUM(%s), 0))',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_total_invoiced'),
                $this->globalRateResolver->resolvePriceColumn('main_table.base_total_refunded')
            ),
            'entity_id' => "CONCAT(main_table.entity_id,sales_order_address.region,'{$this->createUniqueEntity()}')"
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
        $this->addCountryIdFilter($collection);
    }

    /**
     * @param $collection
     */
    public function addGroupBy($collection)
    {
        $collection->getSelect()
            ->group("sales_order_address.region")
            ->group("sales_order_address.country_id");
        ;
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function addCountryIdFilter($collection)
    {
        $filters = $this->getRequestParams();
        $countryId = isset($filters['country_id']) && $filters['country_id'] ? $filters['country_id'] : false;

        if ($countryId) {
            $collection->getSelect()->where('sales_order_address.country_id = ?', $countryId);
        }
    }
}
