<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Sales\Overview\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var \Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection
     */
    private $filterApplier;

    /**
     * @var array|null
     */
    private $totals = null;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection $filterApplier,
        $mainTable = 'sales_order',
        $resourceModel = \Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);

        $this->filterApplier = $filterApplier;
        $filterApplier->prepareCollection($this);
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        if ($this->totals === null) {
            $this->totals = $this->filterApplier->getTotals($this);
        }

        return $this->totals;
    }
}
