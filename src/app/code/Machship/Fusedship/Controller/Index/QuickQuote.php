<?php

namespace Machship\Fusedship\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
/**
 * Class Index
 * @package Machship\Fusedship\Controller\Index\QuickQuote
 */
class QuickQuote  implements HttpGetActionInterface
{
    protected $jsonResultFactory;

    protected $objectManager;

    protected $fusedshipHelper;

    private $_checkoutSession;

    private $getQuantityInformationPerSource;

    public function __construct(
        JsonFactory $jsonResultFactory,

        // FOR IMPROVEMENTS
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->jsonResultFactory = $jsonResultFactory;

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->fusedshipHelper = $this->objectManager->get('Machship\Fusedship\Helper\Data');

        $this->_checkoutSession = $this->objectManager->get('\Magento\Checkout\Model\Session');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if ($moduleManager->isEnabled('Magento_Inventory')) {
            $this->getQuantityInformationPerSource = $objectManager->get('Magento\InventoryConfigurableProductAdminUi\Model\GetQuantityInformationPerSource');
        }
    }


    public function execute()
    {
        $rates = [];

        if($this->fusedshipHelper->isLiveRatesEnabled()) {
            // Query Live Rates
            $rates = $this->queryLiveRates();

            // Handle Shipping Margin
            $rates = $this->handleShippingOverrides($rates);
        }

        $response = [
            'data' => $rates
        ];

        $result = $this->jsonResultFactory->create();

        return $result->setData($response);
    }


    /**
     * Query Fusedship Live Rates
     *
     * @return array
     */
    public function queryLiveRates()
    {
        $rates = [];

        try {

            $fusedship_integration_key = $this->fusedshipHelper->getFusedshipIntegrationKey() ?? '';

            $url = $this->fusedshipHelper->getApiUrl() . $fusedship_integration_key . '/rate';

            $params = $this->generatePayload();


            $curl = $this->objectManager->get('\Magento\Framework\HTTP\Client\Curl');

            $headers = ["Content-Type" => "application/json"];

            $curl->setHeaders($headers);
            $curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
            $curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
            $curl->post($url, json_encode($params));


            $response = json_decode($curl->getBody(), true);

            $rates = $response['data'] ?? [];

        } catch (\Exception $e) {
            // dd($e->getMessage());
        }

        return $rates;
    }


    /**
     * Generate Live Rates Payload
     *
     * @return array
     */
    private function generatePayload()
    {
        $payload = [
            'rate' => []
        ];

        $payload['rate']['origin'] = [];
        $payload['rate']['destination'] = [];
        $payload['rate']['items'] = [];


        $cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');

        $items = $cart->getQuote()->getAllItems();

        $weightUnit = $this->fusedshipHelper->getWeightUnit() ?? 'lbs';

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

        $request = $this->objectManager->get('Magento\Framework\App\Request\Http');

        $postcode = $request->getParam('postcode');
        $city     = $request->getParam('city');
        $country  = $request->getParam('country');
        $region   = $request->getParam('region');
        $isResi   = $request->getParam('is_residential');

        if (filter_var($isResi, FILTER_VALIDATE_BOOLEAN)) {
            $payload['rate']['is_residential'] = true;
        }

        $destination = [
            'postal_code' => $postcode ?? null,
            'city' => $city ?? null,
            'country' => $country ?? null,
            'province' => $region ?? null,
        ];


        $this->_checkoutSession->setFusedshipDestination($destination);
        $this->_checkoutSession->setData('fusedship_is_residential', $isResi);


        $quick_quote_product_id   = $request->getParam('product_id');
        $quick_quote_qty   = $request->getParam('quantity');

        $cart_items = [];


        if($quick_quote_product_id) {

            $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($quick_quote_product_id);


            if($product) {
                $deployment_config = $this->objectManager->get('Magento\Framework\App\DeploymentConfig');

                $weight = $product->getData('weight') ?? 0;

                if($weightUnit == 'lbs' && !empty($weight)) {
                    $weight = floatval($weight) * 0.454;
                }

                $table_prefix = $deployment_config->get('db/table_prefix');

                // Fusedship Product Cartons
                $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();

                $fusedshipProductCartonsTable  =  $connection->getTableName('fusedship_product_cartons');

                if (!empty($table_prefix) && strpos($fusedshipProductCartonsTable, $table_prefix) === false) {
                    $fusedshipProductCartonsTable = $table_prefix . $fusedshipProductCartonsTable;
                }

                $select = $connection->select()->from($fusedshipProductCartonsTable)
                        ->where('product_id = :product_id');
                $binds = ['product_id' => (int) $quick_quote_product_id];

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

                $binds = ['product_id' => (int) $quick_quote_product_id];

                $fusedship_product_data_arr = $connection->fetchAll($select, $binds);

                $use_fusedship_rates = $fusedship_product_data_arr[0]['use_fusedship_rates'] ?? 0;


                $cart_item = [
                    'product_id' => $quick_quote_product_id,
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'quantity' => $quick_quote_qty,
                    'price' => $product->getPrice(),
                    'weight' => $weight,
                    'live_rate_enabled' => (bool) $use_fusedship_rates
                ];

                if (!empty($this->getQuantityInformationPerSource)) {
                    $cart_item['source_quantity'] = $this->getQuantityInformationPerSource->execute($product->getSku());
                }

                if(!empty($boxes)) {
                    $cart_item['boxes'] = $boxes;
                }

                $cart_items[] = $cart_item;
            }
        }


        $payload['rate']['origin'] = $origin;
        $payload['rate']['destination'] = $destination;
        $payload['rate']['items'] = $cart_items;

        return $payload;
    }

    /**
     * Apply Shipping Margin
     *
     */
    private function applyShippingMargin($value) {
        $new_value = $value;

        $margin_type = $this->fusedshipHelper->getShippingMarginType();
        $margin_value = $this->fusedshipHelper->getShippingMarginValue();

        if ($margin_type == '$') {
            $new_value = floatval($margin_value + $new_value);
        } else {
            $new_value = floatval($new_value + ($new_value * ($margin_value/100)));
        }

        return $new_value;
    }

    /**
     * Apply Shipping Rounding
     *
     */
    private function applyShippingRounding($value) {
        $new_value = $value;

        $rounding_option = $this->fusedshipHelper->getShippingRoundingOption();

        switch($rounding_option) {
            case 'RoundUpNearestDollar':
                $new_value = round($new_value, 0, PHP_ROUND_HALF_UP);
                break;

            case 'RoundDownNearestDollar':
                $new_value = round($new_value, 0, PHP_ROUND_HALF_DOWN);
                break;

            case 'RoundUpOrDown':
                $new_value = round($new_value, 0);
                break;

            case 'RoundCeil':
                $new_value = ceil($new_value);
                break;

            default:
                // DoNotRound
                $new_value = number_format($new_value, 2);
                break;
        }

        return $new_value;
    }

    /**
     * Handle Shipping Overrides
     *
     */
    private function handleShippingOverrides($rates) {

        foreach($rates as &$rate) {

            $amount = 0;

            if(!isset($rate['totalSellPrice'])) {
                $amount = $this->applyShippingRounding($amount);

                $rate['amount'] = $amount;
                continue;
            }

            $amount = $rate['totalSellPrice'];

            // Shipping Margin
            if($this->fusedshipHelper->isShippingMarginEnabled()) {
                $amount = $this->applyShippingMargin($amount);
            }

            // Shipping Rounding Option
            $amount = $this->applyShippingRounding($amount);

            $rate['amount'] = $amount;

        }

        return $rates;
    }
}
