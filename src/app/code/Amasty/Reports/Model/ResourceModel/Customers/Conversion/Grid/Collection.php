<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Customers\Conversion\Grid;

use Amasty\Reports\Model\ResourceModel\Customers\Conversion\Collection as ConversionCollection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Amasty\Reports\Model\ResourceModel\Customers\Conversion\Collection $filterApplier,
        $mainTable = 'customer_visitor',
        $resourceModel = \Amasty\Reports\Model\ResourceModel\Customers\Conversion\Collection::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);

        $filterApplier->prepareCollection($this);
    }

    /**
     * @param string $condition
     * @param int $value
     * @return $this
     */
    public function addPlacedOrdersFilter($condition, $value)
    {
        $condition = $this->_getConditionSql(
            ConversionCollection::ORDERS_AMOUNT,
            [$condition => $value]
        );
        $this->getSelect()->having($condition);
        return $this;
    }
}
