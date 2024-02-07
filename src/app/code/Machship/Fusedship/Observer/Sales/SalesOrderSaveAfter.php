<?php
namespace Machship\Fusedship\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderSaveAfter implements ObserverInterface {
    protected $objectManager;

    protected $_request;

    protected $checkoutSession;

    public function __construct(\Magento\Framework\App\RequestInterface $request, \Magento\Checkout\Model\Session $checkoutSession) {
        $this->_request      = $request;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Sales Order Place After Event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_order   = $observer->getEvent()->getOrder();

        $_orderId = $_order->getId();

        $this->fusedshipHelper = $this->objectManager->get('Machship\Fusedship\Helper\Data');
        $weightUnit = $this->fusedshipHelper->getWeightUnit() ?? 'lbs';

        $fusedship_rates = (array) $this->checkoutSession->getFusedshipRates();

        if($this->fusedshipHelper->fusedshipEnablePlugin()) {
            $connection                 = $this->objectManager->get('\Magento\Framework\App\ResourceConnection');
            $connection                 = $connection->getConnection();
            $connectionResource         = $this->objectManager->create('\Magento\Framework\App\ResourceConnection');
            $fusedshipSalesOrderTable   = $connectionResource->getTableName('fusedship_sales_order');

            // Origin
            $scopeConfig = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

            $origin_region_id =  $scopeConfig->getValue(
                'shipping/origin/region_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $region_code = '';
            if($origin_region_id) {
                $regionFactory = $this->objectManager->get('\Magento\Directory\Model\RegionFactory');
                $region = $regionFactory->create()->load($origin_region_id);

                $region_code = $region->getCode();
            }

            $origin_city = $scopeConfig->getValue(
                'shipping/origin/city',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $origin_postcode = $scopeConfig->getValue(
                'shipping/origin/postcode',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $country_code = $scopeConfig->getValue(
                'shipping/origin/country_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );


            $origin = [
                'postal_code' => $origin_postcode,
                'city' => $origin_city,
                'country' => $country_code,
                'province' => $region_code,
            ];

            // Destination
            $shippingAddress = null;

            if($_order->getShippingAddress()) {
                $shippingAddress = $_order->getShippingAddress()->getData();
            }


            // Use quote order items
            // $quoteRepository = $this->objectManager->get('Magento\Quote\Model\QuoteRepository');
            // $quote = $quoteRepository->get($_order->getQuoteId());


            $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->loadByIdWithoutStore($_order->getQuoteId());

            // $quote = $this->objectManager->get('Magento\Quote\Model\QuoteFactory')->create()->loadByIdWithoutStore($_order->getQuoteId());

            $orderItems = $quote->getAllVisibleItems();

            // Order Items
            // $orderItems = $_order->getAllVisibleItems();

            $cart_items = [];

            $deployment_config = $this->objectManager->get('Magento\Framework\App\DeploymentConfig');

            $table_prefix = $deployment_config->get('db/table_prefix');

            $productRepository = $this->objectManager->get('\Magento\Catalog\Model\ProductRepository');

            foreach($orderItems as $item) {
                $productObj = $productRepository->get($item->getSku());

                $item_product_id = $productObj->getId();
                $weight = $productObj->getData('weight') ?? 0;

                if($weightUnit == 'lbs' && !empty($weight)) {
                    $weight = floatval($weight) * 0.454;
                }

                // Fusedship Product Cartons
                $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();

                $fusedshipProductCartonsTable  =  $connection->getTableName('fusedship_product_cartons');


                if (!empty($table_prefix) && strpos($fusedshipProductCartonsTable, $table_prefix) === false) {
                    $fusedshipProductCartonsTable = $table_prefix . $fusedshipProductCartonsTable;
                }

                $select = $connection->select()->from($fusedshipProductCartonsTable)
                        ->where('product_id = :product_id');
                $binds = ['product_id' => (int) $item_product_id];

                $fusedship_cartons = $connection->fetchAll($select, $binds);

                $boxes = [];

                if(is_array($fusedship_cartons) && count($fusedship_cartons)) {
                    foreach($fusedship_cartons as $fusedship_carton) {
                        $boxes[] = [
                            'length' => $fusedship_carton['carton_length'] ?? 0,
                            'width' => $fusedship_carton['carton_width'] ?? 0,
                            'height' => $fusedship_carton['carton_height'] ?? 0,
                            'weight' => $fusedship_carton['carton_weight'] ?? 0,
                            'type' => $fusedship_carton['package_type'] ?? ''
                        ];
                    }
                }

                // Fusedship Product Data

                $fusedshipProductDataTable  =   $connection->getTableName('fusedship_product_data');

                if (!empty($table_prefix) && strpos($fusedshipProductDataTable, $table_prefix) === false) {
                    $fusedshipProductDataTable = $table_prefix . $fusedshipProductDataTable;
                }

                $select = $connection->select()->from($fusedshipProductDataTable)->where('product_id = :product_id');

                $binds = ['product_id' => (int) $item_product_id];

                $fusedship_product_data_arr = $connection->fetchAll($select, $binds);

                $use_fusedship_rates = $fusedship_product_data_arr[0]['use_fusedship_rates'] ?? 0;


                $cart_item = [
                    'product_id' => $item_product_id,
                    'name' => $item->getName(),
                    'sku' => $item->getSku(),
                    'quantity' => $item->getQty(),
                    'price' => $item->getPrice(),
                    'weight' => $weight,
                    'live_rate_enabled' => (bool) $use_fusedship_rates,

                ];

                if(!empty($boxes)) {
                    $cart_item['boxes'] = $boxes;
                }

                $cart_items[] = $cart_item;
            }


            $fusedship_origin = json_encode($origin, JSON_HEX_QUOT   | JSON_HEX_APOS);
            $fusedship_destination = json_encode($shippingAddress, JSON_HEX_QUOT   | JSON_HEX_APOS);
            $fusedship_order_items = json_encode($cart_items, JSON_HEX_QUOT   | JSON_HEX_APOS);


            if (!empty($table_prefix) && strpos($fusedshipSalesOrderTable, $table_prefix) === false) {
                $fusedshipSalesOrderTable = $table_prefix . $fusedshipSalesOrderTable;
            }



            $fusedshipSalesOrderFound = $connection->select('order_id')
            ->from($fusedshipSalesOrderTable)
            ->where('order_id = :order_id');

            $binds = ['order_id' => (int) $_orderId];

            $fusedshipSalesOrderFound = $connection->fetchAll($fusedshipSalesOrderFound, $binds);

            if (isset($fusedshipSalesOrderFound) && !empty($fusedshipSalesOrderFound)) {
                // Do nothing
            } else {
                $fusedship_is_residential = filter_var($this->checkoutSession->getData('fusedship_is_residential'), FILTER_VALIDATE_BOOLEAN) ? 1: 0;

                $sql    = "INSERT INTO " . $fusedshipSalesOrderTable . " (order_id, fusedship_origin, fusedship_destination, fusedship_order_items, fusedship_is_residential) Values ('".$_orderId."','".$fusedship_origin."','".$fusedship_destination."','".$fusedship_order_items."',". $fusedship_is_residential .")";
                $connection->query($sql);
            }
        }
    }
}
