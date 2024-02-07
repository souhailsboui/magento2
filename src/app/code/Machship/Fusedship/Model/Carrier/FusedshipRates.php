<?php

namespace Machship\Fusedship\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class FusedshipRates extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'fusedshiprates';


    protected $_isFixed = true;

    private $_rateResultFactory;
    private $_rateMethodFactory;
    private $_checkoutSession;
    private $_msFusedshipLogger;
    private $_sourceRepository;
    private $_objectManager;

    private $_getQuantityInformationPerSource;
    private $_fusedshipHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Machship\Fusedship\Logger\Logger $msFusedshiplogger,

        // FOR IMPROVEMENTS
        \Magento\Framework\Module\Manager $moduleManager,


        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_checkoutSession   = $checkoutSession;
        $this->_msFusedshipLogger = $msFusedshiplogger;

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if ($moduleManager->isEnabled('Magento_Inventory')) {
            $this->_sourceRepository = $this->_objectManager->get('\Magento\InventoryApi\Api\SourceRepositoryInterface');;
            $this->_getQuantityInformationPerSource = $this->_objectManager->get('Magento\InventoryConfigurableProductAdminUi\Model\GetQuantityInformationPerSource');
        }

        $this->_fusedshipHelper = $this->_objectManager->get('Machship\Fusedship\Helper\Data');

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => 'Fusedship Rates'];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {

        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][collectRates] init2 ', $request);
        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][collectRates] getPackageWeight ', $request->getPackageWeight());
        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][collectRates] getPackageQty ', $request->getPackageQty());

        $result = $this->_rateResultFactory->create();

        // GOAL is to generate payload
        $payload = [
            'rate' => [
                'origin' => $this->generateOrigin($request),
                'destination' => [
                    'postal_code' => $request->getDestPostcode() ?? null,
                    'city' => $request->getDestCity() ?? null,
                    'country' => $request->getDestCountryId() ?? null,
                    'province' => $request->getDestRegionCode() ?? null
                ]
            ]
        ];

        // now validate data so we can prevent spam
        if (!$this->isValidAddress($payload['rate']['destination'])) {

            if (
                !empty($payload['rate']['destination']['country']) &&
                $payload['rate']['destination']['country'] != 'AU'
            ) {
                return $result;
            }

            // try to get from session
            $payload['rate']['destination'] = $this->_checkoutSession->getFusedshipDestination();

            // if still not valid lets stop
            if (!$this->isValidAddress($payload['rate']['destination'])) {
                return $result;
            }

        }

        // save destination address to session
        $this->_checkoutSession->setFusedshipDestination($payload['rate']['destination']);


        // prep items
        $items = $request->getAllItems();
        foreach ($items as $item) {
            $payload['rate']['items'][] = $this->generateItem($item);
        }

        $this->checkItemsForCurrentSource($payload);

        // add if its residential or not
        $isResidential = $this->_checkoutSession->getData('fusedship_is_residential') ?? null;
        if (!is_null($isResidential)) {
            $payload['rate']['is_residential'] = $isResidential;
        }

        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][collectRates] new payload ', $payload);



        // NEXT request to fusedship liverate
        $liverateResult = $this->requestFusedshipLiverate($payload);
        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][collectRates] result ', $liverateResult);

        $rates = $this->handleShippingOverrides($liverateResult['data']);

        // NEXT set shipping methods


        foreach($rates as $rate) {

            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($rate['method_title'] ?? 'Fusedship Rates');
            $method->setMethod($rate['method_code'] ?? $this->_code);
            $method->setMethodTitle($rate['carrier_title'] ?? 'Fusedship Rates');

            $amount = $rate['amount'] ?? 0;

            $method->setPrice($amount);
            $method->setCost($amount);

            $result->append($method);

            $this->_msFusedshipLogger->writeDebug('[FusedshipRates][collectRates] rate appended ', [
                'method' => $rate['method_code'],
                'title' => $rate['carrier_title'],
                'price' => $amount
            ]);
        }

        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][collectRates] completed');


        return $result;
    }

    private function checkItemsForCurrentSource(array &$payload): void
    {
        // New! since we can't guarante through source_code alone in compare with state
        // we have to get each sources address detail to get state w/ postal

        $items = $payload['rate']['items'];

        if (empty($items[0]['source_quantity']) || empty($this->_sourceRepository)) {
            // no need to continue below if inventory is not implemented
            return;
        }

        // loop through the sources
        $sources = [];
        foreach ($items[0]['source_quantity'] as $source) {
            $sourceDetail = $this->_sourceRepository->get($source['source_code']);

            $sourcePostcode = $sourceDetail->getPostcode();
            $sourceCity = $sourceDetail->getCity();

            $code = $sourcePostcode . "_" . $sourceCity;

            $sources[$code] = $source['source_code'];

        }

        $originCode = $payload['rate']['origin']['postal_code'] . "_" . $payload['rate']['origin']['city'];

        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][checkItemsForCurrentSource] origin code', $originCode);
        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][checkItemsForCurrentSource] sources', $sources);
        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][checkItemsForCurrentSource] items', $items);

        // this condition should be here
        if (empty($sources[$originCode])) {
            $this->_msFusedshipLogger->writeDebug('[FusedshipRates][checkItemsForCurrentSource] empty sources with originCode', $originCode);
            return;
        }


        // Goal is to identify if all item stocks are available in the current state warehouse
        // If not lets removed it so the next request would handle it

        // loop through the items
        // check if all items is available in the origin state
        // if not, then lets strip them off
        // but if everything can be found in the origin state then use it
        $removeItems = [];



        // first we need to determine if there are items that has zero stocks in this current source
        $hasOutOfStocks = false;

        foreach ($items as &$item) {
            if (empty($item['source_quantity'])) {
                continue;
            }

            $sourceCode = $sources[$originCode];
            $currentWarehouse = array_filter($item['source_quantity'], function($source) use ($sourceCode) {
                return $source['source_code'] == $sourceCode;
            });

            $currentWarehouse = array_values($currentWarehouse)[0] ?? [];

            $item['current_warehouse'] = $currentWarehouse;

            if (!empty($currentWarehouse) && $currentWarehouse['quantity_per_source'] == 0) {
                $hasOutOfStocks = true;
                $item['out_of_stock'] = true;
            }

        }

        unset($item);


        $newItems = [];

        foreach ($items as $key => $item) {
            if (empty($item['current_warehouse'])) {
                // stop if source_quantity is unavailable
                continue;
            }

            if ($hasOutOfStocks) {

                // if this item is out of stock! lets stop
                if (!empty($item['out_of_stock'])) {
                    continue;
                }

                // if the current warehouse has stocks
                if ($item['current_warehouse']['quantity_per_source'] > 0) {
                    foreach ($item['source_quantity'] as $source) {
                        // we need to check other warehouse if its available
                        if (
                            $source['source_code'] != 'default' &&
                            $source['source_code'] != $sources[$originCode] &&
                            $source['quantity_per_source'] > 0
                        ) {
                            // then no need to add to new items
                            continue 2;
                        }
                    }
                }


            }

            $newItems[] = $item;

        }

        $payload['rate']['items'] = $newItems;

    }

    private function generateOrigin($request) {
        $scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $originRegionId =  $request->getOrigRegionCode() ?? $scopeConfig->getValue(
            'shipping/origin/region_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $regionCode = '';
        if($originRegionId) {
            $regionFactory = $this->_objectManager->get('\Magento\Directory\Model\RegionFactory');
            $region = $regionFactory->create()->load($originRegionId);

            $regionCode = $region->getCode();
        }

        $originCity = $scopeConfig->getValue(
            'shipping/origin/city',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $originPostcode = $scopeConfig->getValue(
            'shipping/origin/postcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $countryCode = $scopeConfig->getValue(
            'shipping/origin/country_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return [
            'postal_code' => $request->getOrigPostcode() ?? $originPostcode,
            'city'        => $request->getCity() ?? $originCity,
            'country'     => $request->getOrigCountry() ?? $countryCode,
            'province'    => $regionCode,
        ];
    }

    private function generateItem($item) {
        // Fusedship Product Cartons

        // prepare resources and connection
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $deploymentConfig = $this->_objectManager->get('Magento\Framework\App\DeploymentConfig');
        $connection = $resource->getConnection();

        // prepare tables and prefix
        $fusedshipProductCartonsTable  =  $connection->getTableName('fusedship_product_cartons');
        $fusedshipProductDataTable  =   $connection->getTableName('fusedship_product_data');

        $tablePrefix = $deploymentConfig->get('db/table_prefix');

        if (!empty($tablePrefix) && strpos($fusedshipProductCartonsTable, $tablePrefix) === false) {
            $fusedshipProductCartonsTable = $tablePrefix . $fusedshipProductCartonsTable;
            $fusedshipProductDataTable = $tablePrefix . $fusedshipProductDataTable;
        }

        // prepare query
        $binds = ['product_id' => (int) $item->getProductId()];

        $select = $connection->select()
            ->from($fusedshipProductCartonsTable)
            ->where('product_id = :product_id');

        $fusedshipCartons = $connection->fetchAll($select, $binds);

        $boxes = [];

        if (is_array($fusedshipCartons) && count($fusedshipCartons)) {

            foreach($fusedshipCartons as $carton) {
                $boxes[] = [
                    'length' => $carton['carton_length'] ?? 0,
                    'width'  => $carton['carton_width'] ?? 0,
                    'height' => $carton['carton_height'] ?? 0,
                    'weight' => $carton['carton_weight'] ?? 0,
                    'type'   => $carton['package_type'] ?? ''
                ];
            }

        }

        $select = $connection->select()->from($fusedshipProductDataTable)->where('product_id = :product_id');
        $fsProduct = $connection->fetchAll($select, $binds);
        $isLiverateEnabled = $fsProduct[0]['use_fusedship_rates'] ?? 0;

        $itemDetail = [
            'product_id' => $item->getProductId(),
            'name'       => $item->getName(),
            'sku'        => $item->getSku(),
            'quantity'   => $item->getQty(),
            'price'      => $item->getPrice(),
            'weight'     => $item->getWeight(),
            'live_rate_enabled' => (bool) $isLiverateEnabled,
            'boxes'      => $boxes
        ];

        if (!empty($this->_getQuantityInformationPerSource)) {
            $itemDetail['source_quantity'] = $this->_getQuantityInformationPerSource->execute($item->getSku());
        }

        return $itemDetail;

    }

    private function requestFusedshipLiverate($payload) {
        try {
            $fskey = $this->_fusedshipHelper->getFusedshipIntegrationKey() ?? '';
            $url   = $this->_fusedshipHelper->getApiUrl() . $fskey . '/rate';
            $curl  = $this->_objectManager->get('\Magento\Framework\HTTP\Client\Curl');
            $curl->setHeaders(["Content-Type" => "application/json"]);
            $curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
            $curl->setOption(CURLOPT_SSL_VERIFYPEER,false);

            $curl->post($url, json_encode($payload));
            $response = json_decode($curl->getBody(), true);
            return $response;
        } catch (\Exception $e) {
            $this->_msFusedshipLogger->writeDebug('[FusedshipRates][requestFusedshipLiverate] error ' . $e->getMessage());

            return [];
        }
    }

    private function handleShippingOverrides($rates) {

        $this->_msFusedshipLogger->writeDebug('[FusedshipRates][handleShippingOverrides] init');

        foreach($rates as &$rate) {

            $amount = 0;

            if(!isset($rate['totalSellPrice'])) {
                $amount = $this->applyShippingRounding($amount);

                $rate['amount'] = $amount;
                continue;
            }

            $amount = $rate['totalSellPrice'];

            // Shipping Margin
            if($this->_fusedshipHelper->isShippingMarginEnabled()) {
                $amount = $this->applyShippingMargin($amount);
            }

            // Shipping Rounding Option
            $amount = $this->applyShippingRounding($amount);

            $rate['amount'] = $amount;

        }

        unset($rate);

        $this->_msFusedshipLogger->writeDebug('[LiveRates][handleShippingOverrides] completed : ', $rates);

        return $rates;
    }

    private function applyShippingRounding($value) {
        $new_value = $value;

        $rounding_option = $this->_fusedshipHelper->getShippingRoundingOption();

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
     * Apply Shipping Margin
     *
     */
    private function applyShippingMargin($value) {
        $new_value = $value;

        $margin_type = $this->_fusedshipHelper->getShippingMarginType();
        $margin_value = $this->_fusedshipHelper->getShippingMarginValue();

        if ($margin_type == '$') {
            $new_value = floatval($margin_value + $new_value);
        } else {
            $new_value = floatval($new_value + ($new_value * ($margin_value/100)));
        }

        return $new_value;
    }

    private function isValidAddress($address) {
        return !empty($address['postal_code']) &&
            !empty($address['city']) &&
            !empty($address['country']) &&
            $address['country'] === 'AU';
    }
}
