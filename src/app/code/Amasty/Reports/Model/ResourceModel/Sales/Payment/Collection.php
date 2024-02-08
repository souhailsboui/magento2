<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Sales\Payment;

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
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;

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
        \Magento\Payment\Helper\Data $paymentHelper,
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
        $this->paymentHelper = $paymentHelper;
        $this->globalRateResolver = $globalRateResolver;
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Sales\Payment\Grid\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinPaymentTable($collection)
    {
        $collection->getSelect()
            ->columns(['period' => 'payment.method'])
            ->join(
                ['payment' => $this->getTable('sales_order_payment')],
                'payment.parent_id = main_table.entity_id'
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
        $this->joinPaymentTable($collection);

        $collection->getSelect()
            ->columns([
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
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('main_table.base_total_invoiced')
                ),
                'refunded' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('main_table.base_total_refunded')
                ),
                'entity_id' => 'CONCAT(main_table.entity_id,\''.$this->createUniqueEntity().'\')'
            ])
        ;
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
            ->group("payment.method")
        ;
    }

    /**
     * @return $this|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad(); // TODO: Change the autogenerated stub

        $paymentMethods = $this->paymentHelper->getPaymentMethods();
        foreach ($this->_items as $item) {
            $item->setPeriod($paymentMethods[$item->getPeriod()]['title']);
        }
        return $this;
    }
}
