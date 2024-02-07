<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model;

use Amasty\Reports\Model\Sales\FormatPrice;
use Amasty\Reports\Model\Source\Status;
use Amasty\Reports\Model\Store as StoreResolver;
use Amasty\Reports\Model\Utilities\Order\GlobalRateResolver;
use Magento\Framework\Model\AbstractModel;
use Amasty\Reports\Model\ResourceModel\Report\Order\Collection;
use Amasty\Reports\Model\ResourceModel\Report\Order\CollectionFactory;

class Widget extends AbstractModel
{
    public const TOTAL_WIDGET = 'total';
    public const LIVE_WIDGET = 'live';
    public const WIDGET_TOTAL_ORDERS = 'total_orders';
    public const WIDGET_TOTAL_SALES = 'total_sales';
    public const WIDGET_TOTAL_CUSTOMERS = 'total_customers';
    public const WIDGET_TOTAL_ITEMS = 'total_items';
    public const WIDGET_TOTAL_REFUNDED = 'total_refunded';
    public const WIDGET_TOTAL_ABANDONED = 'total_abandoned';
    public const WIDGET_AVG_SALES = 'average_sales';
    public const WIDGET_LIVE_ITEMS = 'items_purchased';
    public const WIDGET_LIVE_ORDERS = 'orders_placed';
    public const WIDGET_LIVE_REVENUE = 'revenue';
    public const WIDGET_LIVE_VISITORS = 'unique_visitors';
    public const WIDGET_LIVE_CARTS = 'active_carts';
    public const WIDGET_LIVE_ITEMS_CART = 'items_active_carts';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configInterface;

    /**
     * @var CollectionFactory
     */
    private $ordersCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    private $customerCollection;
    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $saveConfigInterface;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory
     */
    private $visitorCollectionFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;
    /**
     * @var ResourceModel\Abandoned\Cart\CollectionFactory
     */
    private $cartCollectionFactory;

    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @var GlobalRateResolver
     */
    private $globalRateResolver;

    /**
     * @var FormatPrice
     */
    private $formatPrice;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $saveConfigInterface,
        CollectionFactory $ordersCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection,
        \Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory $visitorCollectionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Amasty\Reports\Model\ResourceModel\Abandoned\Cart\CollectionFactory $cartCollectionFactory,
        StoreResolver $storeResolver,
        GlobalRateResolver $globalRateResolver,
        FormatPrice $formatPrice,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->configInterface = $configInterface;
        $this->ordersCollectionFactory = $ordersCollectionFactory;
        $this->customerCollection = $customerCollection;
        $this->saveConfigInterface = $saveConfigInterface;
        $this->visitorCollectionFactory = $visitorCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cartCollectionFactory = $cartCollectionFactory;
        $this->storeResolver = $storeResolver;
        $this->globalRateResolver = $globalRateResolver;
        $this->formatPrice = $formatPrice;
    }

    /**
     * @param string $group
     *
     * @return array
     */
    public function getCurrentWidgets($group)
    {
        $allWidgets = $this->getWidgets($group);
        $activeWidget1 = $this->getActiveWidget($group, 1);
        $activeWidget2 = $this->getActiveWidget($group, 2);
        $activeWidget3 = $this->getActiveWidget($group, 3);
        $activeWidget4 = $this->getActiveWidget($group, 4);
        $activeWidget5 = $this->getActiveWidget($group, 5);
        $activeWidget6 = $this->getActiveWidget($group, 6);

        return [
            '1' => $allWidgets[$activeWidget1],
            '2' => $allWidgets[$activeWidget2],
            '3' => $allWidgets[$activeWidget3],
            '4' => $allWidgets[$activeWidget4],
            '5' => $allWidgets[$activeWidget5],
            '6' => $allWidgets[$activeWidget6]
        ];
    }

    /**
     * @param $widget
     * @return float|int|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWidgetData($widget)
    {
        $result = 0;
        /** @var Collection $collection */
        $collection = $this->ordersCollectionFactory->create()
            ->addFieldToFilter('state', ['neq' => \Magento\Sales\Model\Order::STATE_CANCELED]);
        if ($this->storeResolver->getCurrentStoreId()) {
            $collection->addFieldToFilter('store_id', $this->storeResolver->getCurrentStoreId());
        }
        switch ($widget) {
            case self::WIDGET_LIVE_ITEMS:
                $collection->addCreateAtFilter();
                // phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
            case self::WIDGET_TOTAL_ITEMS:
                $collection->removeAllFieldsFromSelect()
                    ->addExpressionFieldToSelect('total_qty_ordered', 'SUM({{total_item_count}})', 'total_item_count');
                $result = round($collection->fetchItem()->getTotalQtyOrdered() ?: 0);
                break;
            case self::WIDGET_TOTAL_CUSTOMERS:
                $collection = $this->customerCollection
                    ->removeAllFieldsFromSelect();
                if ($this->storeResolver->getCurrentStoreId()) {
                    $collection->addFieldToFilter('store_id', $this->storeResolver->getCurrentStoreId());
                }
                $result = $collection->getSize();
                break;
            case self::WIDGET_TOTAL_REFUNDED:
                $collection->removeAllFieldsFromSelect()
                    ->addExpressionFieldToSelect(
                        'total_refunded',
                        'SUM({{total_refunded}})',
                        ['total_refunded' => $this->globalRateResolver->resolvePriceColumn('base_total_refunded')]
                    );
                $result = $collection->fetchItem()->getTotalRefunded();
                $result = $this->formatPrice->execute((float) $result);
                break;
            case self::WIDGET_LIVE_ORDERS:
                $collection->addCreateAtFilter();
                // phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
            case self::WIDGET_TOTAL_ORDERS:
                $collection->removeAllFieldsFromSelect()
                    ->addOrdersCount();
                $result = round($collection->fetchItem()->getOrdersCount() ?: 0);
                break;
            case self::WIDGET_TOTAL_ABANDONED:
                /** @var \Amasty\Reports\Model\ResourceModel\Abandoned\Cart\Collection $cartCollection */
                $cartCollection = $this->cartCollectionFactory->create()->addFieldToFilter(
                    \Amasty\Reports\Model\ResourceModel\Abandoned\Cart::STATUS,
                    Status::PROCESSING
                );
                if ($this->storeResolver->getCurrentStoreId()) {
                    $cartCollection->addFieldToFilter('store_id', $this->storeResolver->getCurrentStoreId());
                }
                $result = $cartCollection->getSize();
                break;
            case self::WIDGET_LIVE_REVENUE:
                $collection->addCreateAtFilter();
            // phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
            case self::WIDGET_TOTAL_SALES:
                $collection->removeAllFieldsFromSelect()
                    ->addExpressionFieldToSelect(
                        'base_grand_total',
                        'SUM({{base_grand_total}})',
                        ['base_grand_total' => $this->globalRateResolver->resolvePriceColumn('base_grand_total')]
                    );
                $result = $collection->fetchItem()->getBaseGrandTotal();
                $result = $this->formatPrice->execute((float) $result);
                break;
            case self::WIDGET_LIVE_VISITORS:
                $result = $this->visitorCollectionFactory->create()->addFieldToFilter(
                    'last_visit_at',
                    ['gteq' => new \Zend_Db_Expr('NOW() - INTERVAL 10 MINUTE')]
                )->getSize();
                break;
            case self::WIDGET_LIVE_CARTS:
                $quoteCollection = $this->quoteCollectionFactory->create()->addFieldToFilter(
                    'updated_at',
                    ['gteq' => new \Zend_Db_Expr('NOW() - INTERVAL 10 MINUTE')]
                )->addFieldToFilter(
                    'items_count',
                    ['gteq' => 1]
                );
                if ($this->storeResolver->getCurrentStoreId()) {
                    $quoteCollection->addFieldToFilter('store_id', $this->storeResolver->getCurrentStoreId());
                }
                $result = $quoteCollection->getSize();
                break;
            case self::WIDGET_LIVE_ITEMS_CART:
                $quoteCollection = $this->quoteCollectionFactory->create()->addFieldToFilter(
                    'updated_at',
                    ['gteq' => new \Zend_Db_Expr('NOW() - INTERVAL 10 MINUTE')]
                )->addExpressionFieldToSelect(
                    'items_count',
                    'SUM({{items_count}})',
                    'items_count'
                );
                if ($this->storeResolver->getCurrentStoreId()) {
                    $quoteCollection->addFieldToFilter('store_id', $this->storeResolver->getCurrentStoreId());
                }
                $result = round($quoteCollection->fetchItem()->getItemsCount() ?: 0);
                break;
            case self::WIDGET_AVG_SALES:
                $collection->removeAllFieldsFromSelect()
                    ->addExpressionFieldToSelect(
                        'base_grand_total',
                        'AVG({{base_grand_total}})',
                        ['base_grand_total' => $this->globalRateResolver->resolvePriceColumn('base_grand_total')]
                    );
                $result = $collection->fetchItem()->getBaseGrandTotal();
                $result = $this->formatPrice->execute((float) $result);
                break;
        }
        return $result;
    }

    /**
     * @param $group
     * @param $number
     *
     * @return mixed
     */
    public function getActiveWidget($group, $number)
    {
        return $this->configInterface->getValue('amreports/widgets/' . $group . '/widget' . $number);
    }

    /**
     * @param $group
     * @param $number
     * @param $name
     * @return \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    public function changeWidget($group, $number, $name)
    {
        return $this->saveConfigInterface->saveConfig(
            'amreports/widgets/' . $group . '/widget' . $number,
            $name,
            'default',
            0
        );
    }

    /**
     * @param string $group
     *
     * @return array
     */
    public function getWidgets($group)
    {
        $widgets = [
            self::TOTAL_WIDGET => [
                self::WIDGET_TOTAL_ORDERS => [
                    'name'  => self::WIDGET_TOTAL_ORDERS,
                    'title' => __('Orders'),
                    'link'  => 'amasty_reports/report_sales/orders'
                ],
                self::WIDGET_TOTAL_SALES => [
                    'name'  => self::WIDGET_TOTAL_SALES,
                    'title' => __('Sales'),
                    'link'  => 'amasty_reports/report_sales/orders'
                ],
                self::WIDGET_AVG_SALES => [
                    'name' => self::WIDGET_AVG_SALES,
                    'title' => __('Average Order Value'),
                    'link' => 'amasty_reports/report_sales/orderItems'
                ],
                self::WIDGET_TOTAL_CUSTOMERS => [
                    'name'  => self::WIDGET_TOTAL_CUSTOMERS,
                    'title' => __('Customers'),
                    'link'  => 'amasty_reports/report_customers/customers'
                ],

                self::WIDGET_TOTAL_ITEMS => [
                    'name'  => self::WIDGET_TOTAL_ITEMS,
                    'title' => __('Items Ordered'),
                    'link'  => 'amasty_reports/report_sales/orders'
                ],
                self::WIDGET_TOTAL_REFUNDED  => [
                    'name'  => self::WIDGET_TOTAL_REFUNDED,
                    'title' => __('Refunded'),
                    'link'  => 'amasty_reports/report_sales/orders'
                ],
                self::WIDGET_TOTAL_ABANDONED => [
                    'name'  => self::WIDGET_TOTAL_ABANDONED,
                    'title' => __('Abandoned Cart'),
                    'link'  => 'amasty_reports/report_customers/abandoned'
                ]
            ],
            self::LIVE_WIDGET  => [
                self::WIDGET_LIVE_ITEMS => [
                    'name'  => self::WIDGET_LIVE_ITEMS,
                    'title' => __('Items purchased')
                ],
                self::WIDGET_LIVE_ORDERS => [
                    'name'  => self::WIDGET_LIVE_ORDERS,
                    'title' => __('Orders placed')
                ],
                self::WIDGET_LIVE_REVENUE => [
                    'name'  => self::WIDGET_LIVE_REVENUE,
                    'title' => __('Revenue')
                ],

                self::WIDGET_LIVE_VISITORS => [
                    'name'  => self::WIDGET_LIVE_VISITORS,
                    'title' => __('Unique visitors right now')
                ],
                self::WIDGET_LIVE_CARTS  => [
                    'name'  => self::WIDGET_LIVE_CARTS,
                    'title' => __('Active shopping carts')
                ],
                self::WIDGET_LIVE_ITEMS_CART => [
                    'name'  => self::WIDGET_LIVE_ITEMS_CART,
                    'title' => __('Qty of items added to shopping cart')
                ]
            ]
        ];

        return $widgets[$group];
    }

    /**
     * @return array
     */
    public function getWidgetGroups()
    {
        return [
            self::TOTAL_WIDGET,
            self::LIVE_WIDGET
        ];
    }
}
