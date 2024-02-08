<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Catalog\ByAttributes;

use Amasty\Reports\Block\Adminhtml\Report\Catalog\ByAttributes\Toolbar;
use Amasty\Reports\Model\ResourceModel\Filters\AddFromFilter;
use Amasty\Reports\Model\ResourceModel\Filters\AddStoreFilter;
use Amasty\Reports\Model\ResourceModel\Filters\AddToFilter;
use Amasty\Reports\Model\ResourceModel\Filters\RequestFiltersProvider;
use Amasty\Reports\Model\Utilities\CreateUniqueHash;
use Amasty\Reports\Model\Utilities\JoinCustomAttribute;
use Amasty\Reports\Model\Utilities\Order\GlobalRateResolver;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Psr\Log\LoggerInterface;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection
{
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
     * @var JoinCustomAttribute
     */
    private $joinCustomAttribute;

    /**
     * @var CreateUniqueHash
     */
    private $createUniqueHash;

    /**
     * @var RequestFiltersProvider
     */
    private $filtersProvider;

    /**
     * @var string
     */
    protected $_idFieldName = '';

    /**
     * @var GlobalRateResolver
     */
    private $globalRateResolver;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Snapshot $entitySnapshot,
        AddFromFilter $addFromFilter,
        AddToFilter $addToFilter,
        AddStoreFilter $addStoreFilter,
        JoinCustomAttribute $joinCustomAttribute,
        CreateUniqueHash $createUniqueHash,
        RequestFiltersProvider $filtersProvider,
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
            $connection,
            $resource
        );

        $this->addFromFilter = $addFromFilter;
        $this->addToFilter = $addToFilter;
        $this->addStoreFilter = $addStoreFilter;
        $this->joinCustomAttribute = $joinCustomAttribute;
        $this->createUniqueHash = $createUniqueHash;
        $this->filtersProvider = $filtersProvider;
        $this->globalRateResolver = $globalRateResolver;
    }

    /**
     * @param $collection
     */
    public function prepareCollection($collection)
    {
        $this->joinEavAttribute($collection);
        $this->joinChilds($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinEavAttribute($collection)
    {
        $filters = $this->filtersProvider->execute();
        $eav = isset($filters['eav']) ? $filters['eav'] : 'name';

        if ($eav == Toolbar::ATTRIBUTE_SET) {
            $value = 'eas.attribute_set_name';
            $entityId = 'CONCAT(eas.attribute_set_id,cpe.entity_id,\'' . $this->createUniqueHash->execute() .'\')';
            $this->joinAttributeSet($collection);
        } else {
            $value = sprintf('eaov1_%1$s.value', $eav);
            $entityId = sprintf(
                'CONCAT(eaov1_%1$s.value,\'' . $this->createUniqueHash->execute() . '\')',
                $eav
            );
            $this->joinCustomAttribute->execute($collection, $eav);
            $collection->getSelect()->where(
                sprintf('(eaov1_%1$s.value IS NOT NULL)', $eav)
            );
        }

        if ($this->globalRateResolver->isDefaultStore()) {
            $collection->getSelect()->join(
                ['so' => $this->getTable('sales_order')],
                'main_table.order_id = so.entity_id',
                []
            );
        }

        $collection->getSelect()->reset(Select::COLUMNS);
        $collection->getSelect()->columns([
            'name' => $value,
            'total_orders' => 'COUNT(DISTINCT main_table.order_id)',
            'items_ordered' => 'COUNT(main_table.qty_ordered)',
            'qty' => 'FLOOR(SUM(main_table.qty_ordered))',
            'entity_id' => $entityId,
            'total' => sprintf(
                'SUM(IF(soi.total IS NOT NULL AND soi.total != 0, soi.total, %s))',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_row_total')
            ),
            'tax' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_tax_amount')
            ),
            'discounts' => sprintf(
                'SUM(IF(soi.base_discount_amount IS NOT NULL AND soi.base_discount_amount != 0, '
                . 'soi.base_discount_amount, %s))',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_discount_amount')
            ),
            'invoiced' => sprintf(
                'SUM(IF(soi.invoiced IS NOT NULL AND soi.invoiced != 0, soi.invoiced, %s))',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_row_invoiced')
            ),
            'refunded' => sprintf(
                'SUM(IF(soi.refunded IS NOT NULL AND soi.refunded != 0, soi.refunded, %s))',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_amount_refunded')
            )
        ])->where(
            'main_table.parent_item_id IS NULL'
        );
    }

    /**
     * @param $collection
     */
    private function joinAttributeSet($collection)
    {
        $collection->getSelect()
            ->joinleft(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'cpe.entity_id = main_table.product_id'
            )
            ->joinLeft(
                ['eas' => $this->getTable('eav_attribute_set')],
                'cpe.attribute_set_id = eas.attribute_set_id'
            )
            ->group('cpe.attribute_set_id');
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter->execute($collection);
        $this->addToFilter->execute($collection);
        $this->addStoreFilter->execute($collection);
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return $this|\Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    public function addItem(\Magento\Framework\DataObject $item)
    {
        parent::_addItem($item); // TODO: Change the autogenerated stub
        return $this;
    }

    /**
     * @param AbstractCollection $collection
     */
    private function joinChilds($collection)
    {
        $childsSelect = $this->getConnection()->select()->from(
            ['soi' => $this->getTable('sales_order_item')],
            ['parent_item_id']
        )->group(
            'parent_item_id'
        );

        if ($this->globalRateResolver->isDefaultStore()) {
            $childsSelect->join(
                ['so' => $this->getTable('sales_order')],
                'soi.order_id = so.entity_id',
                []
            );
        }

        $childsSelect->columns([
            'total' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('soi.base_row_total')
            ),
            'invoiced' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('soi.base_row_invoiced')
            ),
            'refunded' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('soi.base_amount_refunded')
            ),
            'base_discount_amount' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('soi.base_discount_amount')
            )
        ]);

        $collection->getSelect()->joinLeft(
            ['soi' => $childsSelect],
            'soi.parent_item_id = main_table.item_id',
            ''
        );
    }
}
