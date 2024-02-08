<?php

namespace Machship\Fusedship\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Class Index
 * @package Machship\Fusedship\Controller\Index\AddressLookup
 */
class AddressLookup implements HttpGetActionInterface
{
    protected $jsonResultFactory;

    protected $objectManager;


    public function __construct(\Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory) {
        $this->jsonResultFactory = $jsonResultFactory;

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->fusedshipHelper = $this->objectManager->get('Machship\Fusedship\Helper\Data');
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();


        $request = $this->objectManager->get('Magento\Framework\App\Request\Http');

        $q = $request->getParam('q');

        // we have to check if q
        $qExplode = explode(',', $q);
        if (count($qExplode) > 2) {
            $q = trim($qExplode[0]);
        }


        $json_data = $this->queryAddress($q);

        $data = $json_data['object'] ?? [];

        $data_resp = [];

        foreach($data as $d) {

            // Magento country_id and region_id
            $state_code = $d['state']['code'] ?? null;
            $country_code = $d['country']['code2'] ?? null;

            $regionData = $this->getRegionDataByCode($state_code, $country_code);;

            $d['magentoRegionData'] = $regionData;

            $data_resp[] = $d;
        }


        $rates = json_decode('[
            {
              "carrier_code": "fusedshiprates",
              "method_code": "fusedshiprates1",
              "carrier_title": "Fusedship Rates 1",
              "method_title": "Shipping for items over 25kg where the receiver does NOT have a forklift",
              "amount": 31,
              "base_amount": 31,
              "available": true,
              "error_message": "",
              "price_excl_tax": 31,
              "price_incl_tax": 31
            }
        ]', true);

        // $this->checkoutSession->setFusedshipRates( $rates );


        $response = [
            'data' => $data_resp
        ];

        return $result->setData($response);
    }

    public function queryAddress($q) {
        $results = [];

        try {

            $fusedship_integration_key = $this->fusedshipHelper->getFusedshipIntegrationKey() ?? '64bfdecbf4-69be-4a78-9a12-9472f21b8f94';

            $url = $this->fusedshipHelper->getApiUrl() . $fusedship_integration_key . '/lookup/location';

            $params = json_encode([
                's' => $q
            ]);

            $curl = $this->objectManager->get('\Magento\Framework\HTTP\Client\Curl');

            $headers = ["Content-Type" => "application/json"];

            $curl->setHeaders($headers);
            $curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
            $curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
            $curl->post($url, $params);


            $response = json_decode($curl->getBody(), true);

            $results = $response['data'] ?? [];

        } catch (\Exception $e) {
            // dd($e->getMessage());
        }

        return $results;
    }

    public function getRegionDataByCode($state, $countryId = 'AU') {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $region = $objectManager->create('Magento\Directory\Model\Region')
                        ->loadByCode($state, $countryId);

            return $region->getData();
        }
        catch(\Exception $e) {

            return false;
        }
    }
}
