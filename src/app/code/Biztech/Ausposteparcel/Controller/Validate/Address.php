<?php

namespace Biztech\Ausposteparcel\Controller\Validate;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Address extends Action
{
    protected $_encryptor;
    protected $resultJsonFactory;
    protected $scopeConfig;
    protected $regionCollection;
    protected $url;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        JsonFactory $jsonfactory,
        \Magento\Directory\Model\RegionFactory $regionCollection
    ) {
        $this->_encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->regionCollection = $regionCollection;
        $this->resultJsonFactory = $jsonfactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = array();
        $data = $this->getRequest()->getPost();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        if ($data['country_id'] == 'AU') {
            $Accountnumber = $this->_encryptor->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/accountNo', $storeScope));
            $userName = $this->_encryptor->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/apiKey', $storeScope));
            $password = $this->_encryptor->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/password', $storeScope));

            $suburb = $data['city'];
            $regionId = $data['regionId'];
            $region = $this->regionCollection->create()->load($regionId);
            $state = $region->getCode();
            $postcode = $data['postcode'];

            if ($this->scopeConfig->getValue('carriers/ausposteParcel/operationMode', $storeScope) == 3) {
                $this->url = 'https://digitalapi.auspost.com.au/test/shipping/v1/address?suburb=' . urlencode($suburb) . '&state=' . $state . '&postcode=' . $postcode;
            } elseif ($this->scopeConfig->getValue('carriers/ausposteParcel/operationMode', $storeScope) == 1) {
                $this->url = 'https://digitalapi.auspost.com.au/shipping/v1/address?suburb=' . urlencode($suburb) . '&state=' . $state . '&postcode=' . $postcode;
            }
            
            $ch = curl_init($this->url);
            curl_setopt($ch, CURLOPT_USERPWD, $userName . ":" . $password);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Account-Number: ' . $Accountnumber));
            $result = curl_exec($ch);
            $responseData = json_decode($result);

            $returnArray = array();

            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->resultJsonFactory->create();

            if (isset($responseData->errors[0]->code) && isset($responseData->errors[0]->code)) {
                $returnArray['message'] = "ERROR";
                $result->setData($returnArray);
                return $result;  
            }
            if (isset($responseData->found) && sizeof($responseData->results) && in_array(strtolower($suburb), array_map('strtolower', $responseData->results))) {
                $returnArray['message'] = "ERROR";
                $result->setData($returnArray);
                return $result;
            } elseif (!$responseData->found && sizeof($responseData->results)) {
                $suggesstions = implode(', ', $responseData->results);
                $returnArray['message'] = 'Please enter valid City / Suburb for this address. Relative suggestions are as below :';
                $returnArray['suggesstions'] = $suggesstions;
                $result->setData($returnArray);
                return $result;  
            } else {
                $returnArray['message'] = 'Please enter a valid State / Postalcode or correct City / Suburb for the entered address.';
                $returnArray['suggesstions'] = '';
                $result->setData($returnArray);
                return $result;  
            }
        } else {
            $returnArray['message'] = "ERROR";
            $returnArray['suggesstions'] = '';
            $result->setData($returnArray);
            return $result;  
        }
    }
}
