<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Sales\CartRule\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Amasty\Reports\Model\ResourceModel\Sales\CartRule\Collection $filterApplier,
        $mainTable = 'sales_order',
        $resourceModel = \Amasty\Reports\Model\ResourceModel\Sales\CartRule\Collection::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);

        $filterApplier->prepareCollection($this);
    }

    /**
     * @return Select
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();

        $countSelect = $countSelect->getConnection()->select()->from($countSelect, 'COUNT(*)');

        return $countSelect;
    }
}
