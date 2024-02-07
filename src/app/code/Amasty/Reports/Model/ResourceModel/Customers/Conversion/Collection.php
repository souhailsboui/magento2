<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Customers\Conversion;

use Amasty\Reports\Model\ResourceModel\Filters\AddFromFilter;
use Amasty\Reports\Model\ResourceModel\Filters\AddToFilter;
use Amasty\Reports\Model\ResourceModel\Filters\RequestFiltersProvider;
use Amasty\Reports\Model\Utilities\TimeZoneExpressionModifier;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class Collection extends \Magento\Customer\Model\ResourceModel\Visitor\Collection
{
    public const CONVERSION_EXPRESSION = 'ROUND(number_of_orders.amount/COUNT(DISTINCT '
        . 'IFNULL(main_table.customer_id, main_table.visitor_id)) * 100)';

    public const VISITORS_EXPRESSION = 'COUNT(DISTINCT IFNULL(main_table.customer_id, main_table.visitor_id))';

    public const ORDERS_AMOUNT = 'number_of_orders.amount';

    /**
     * @var AddFromFilter
     */
    private $addFromFilter;

    /**
     * @var AddToFilter
     */
    private $addToFilter;

    /**
     * @var RequestFiltersProvider
     */
    private $filtersProvider;

    /**
     * @var TimeZoneExpressionModifier
     */
    private $timeZoneExpressionModifier;

    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AddFromFilter $addFromFilter,
        AddToFilter $addToFilter,
        RequestFiltersProvider $filtersProvider,
        TimeZoneExpressionModifier $timeZoneExpressionModifier,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->addFromFilter = $addFromFilter;
        $this->addToFilter = $addToFilter;
        $this->filtersProvider = $filtersProvider;
        $this->timeZoneExpressionModifier = $timeZoneExpressionModifier;
    }

    public function prepareCollection(AbstractCollection $collection): void
    {
        $this->applyBaseFilters($collection);
    }

    private function applyBaseFilters(AbstractCollection $collection): void
    {
        $collection->getSelect()->reset(Select::COLUMNS);
        [$periodSelect, $group] = $this->getIntervalSelectAndGroupBy($collection, 'main_table.last_visit_at');
        [$orderPeriodSelect, $orderGroup] = $this->getIntervalSelectAndGroupBy($collection, 'main_table.created_at');
        $conversionExpr = self::CONVERSION_EXPRESSION;
        $excludedStates = [Order::STATE_CANCELED, Order::STATE_CLOSED];
        $orderSelect = $this->getConnection()->select()
            ->from(
                ['main_table' => $this->getTable('sales_order')],
                ['amount' => 'COUNT(DISTINCT main_table.entity_id)', 'period' => $orderPeriodSelect]
            )->where(sprintf(
                'main_table.state NOT IN("%s") AND main_table.remote_ip IS NOT NULL',
                implode('","', $excludedStates)
            ))->group($orderGroup);
        $this->addFromFilter->execute($orderSelect, OrderInterface::CREATED_AT);
        $this->addToFilter->execute($orderSelect, OrderInterface::CREATED_AT);

        $collection->getSelect()
            ->columns([
                'period' => $periodSelect,
                'orders' => self::ORDERS_AMOUNT,
                'visitors' => self::VISITORS_EXPRESSION,
                'conversion' => $conversionExpr
            ])
            ->joinLeft(
                ['number_of_orders' => $orderSelect],
                $periodSelect . ' = number_of_orders.period',
                []
            )
            ->group($group);

        $this->addFromFilter->execute($collection, 'last_visit_at');
        $this->addToFilter->execute($collection, 'last_visit_at');
    }

    private function getIntervalSelectAndGroupBy(AbstractCollection $collection, string $field): array
    {
        $filters = $this->filtersProvider->execute();
        $interval = $filters['interval'] ?? 'day';
        $collection->getSelect()->reset(Select::GROUP);

        switch ($interval) {
            case 'year':
                $select = $group = sprintf('YEAR(%s)', $field);
                break;
            case 'month':
                $select = 'CONCAT(YEAR(%1$s), \'-\', MONTH(%1$s))';
                $select = sprintf($select, $field);
                $group = sprintf('MONTH(%s)', $field);
                break;
            case 'week':
                $select = 'CONCAT(ADDDATE(DATE(%1$s), INTERVAL 1-DAYOFWEEK(%1$s) DAY), '
                    . '" - ", ADDDATE(DATE(%1$s), INTERVAL 7-DAYOFWEEK(%1$s) DAY))';
                $select = sprintf($select, $field);
                $group = sprintf('WEEK(%s)', $field);

                break;
            case 'day':
            default:
                $select = $group = sprintf('DATE(%s)', $field);
                break;
        }

        return [$select, $group];
    }
}
