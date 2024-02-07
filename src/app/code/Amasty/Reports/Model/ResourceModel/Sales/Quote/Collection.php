<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Sales\Quote;

use Amasty\Reports\Model\Source\Quote\Status;
use Amasty\Reports\Model\Utilities\Order\GlobalRateResolver;
use Amasty\Reports\Traits\Filters;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Collection
{
    use Filters;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $helper;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var GlobalRateResolver
     */
    private $globalRateResolver;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Snapshot $entitySnapshot,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        \Amasty\Reports\Model\Source\Quote\Status $status,
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
            $connection,
            $resource
        );
        $this->request = $request;
        $this->helper = $helper;
        $this->status = $status;
        $this->globalRateResolver = $globalRateResolver;
    }

    /**
     * @param Grid\Collection|\Amasty\Reports\Model\ResourceModel\Sales\Orders\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param Grid\Collection|\Amasty\Reports\Model\ResourceModel\Sales\Orders\Collection $collection
     */
    private function applyBaseFilters($collection)
    {
        $this->joinAdvancedTables($collection);

        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns([
                'period' => 'amasty_quote.status',
                'total_orders' => 'COUNT(amasty_quote.quote_id)',
                'total_items' => 'SUM(main_table.items_count)',
                'subtotal' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('main_table.base_subtotal')
                ),
                'tax' => 'SUM(quote_item.tax)',
                'shipping' => 'SUM(quote_address.shipping)',
                'total' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('main_table.base_grand_total')
                ),
                'discounts' => sprintf(
                    '(IF(amasty_quote.sum_original_price, SUM(custom_price - %s), 0))',
                    $this->globalRateResolver->resolvePriceColumn('amasty_quote.sum_original_price')
                ),
            ])->group('amasty_quote.status');
    }

    /**
     * @param Grid\Collection|\Amasty\Reports\Model\ResourceModel\Sales\Orders\Collection $collection
     */
    private function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addCurrentStoreFilter($collection);
    }

    /**
     * @param Grid\Collection|\Amasty\Reports\Model\ResourceModel\Sales\Orders\Collection $collection
     */
    private function joinAdvancedTables($collection)
    {
        $collection->getSelect()->joinLeft(
            ['amasty_quote' => $this->getTable('amasty_quote')],
            'main_table.entity_id = amasty_quote.quote_id',
            []
        )->joinLeft(
            ['quote_item' => $this->getQuoteItemsSelect()],
            'amasty_quote.quote_id = quote_item.quote_id',
            []
        )->joinLeft(
            ['quote_address' => $this->getQuoteAddressSelect()],
            'amasty_quote.quote_id = quote_address.quote_id',
            []
        )->where('amasty_quote.status IN (?)', $this->status->getVisibleOnFrontStatuses());
    }

    private function getQuoteItemsSelect(): \Magento\Framework\DB\Select
    {
        return $this->getConnection()->select()->from(
            ['quote_item' => $this->getTable('quote_item')],
            [
                'quote_id' => 'quote_id',
                'tax' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('base_tax_amount')
                ),
                'custom_price' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('custom_price * qty')
                )
            ]
        )->join(
            ['quote' => $this->getTable('quote')],
            'quote_item.quote_id = quote.entity_id',
            []
        )->group(
            'quote_id'
        );
    }

    private function getQuoteAddressSelect(): \Magento\Framework\DB\Select
    {
        return $this->getConnection()->select()->from(
            ['quote_address' => $this->getTable('quote_address')],
            [
                'quote_id' => 'quote_id',
                'shipping' => sprintf(
                    'SUM(%s)',
                    $this->globalRateResolver->resolvePriceColumn('base_shipping_amount')
                ),
            ]
        )->join(
            ['quote' => $this->getTable('quote')],
            'quote_address.quote_id = quote.entity_id',
            []
        )->group(
            'quote_id'
        );
    }
}
