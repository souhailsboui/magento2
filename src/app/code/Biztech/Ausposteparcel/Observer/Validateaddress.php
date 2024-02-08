<?php
namespace Biztech\Ausposteparcel\Observer;

use Magento\Framework\Event\ObserverInterface;

class Validateaddress implements ObserverInterface
{
    protected $scopeConfig;
    protected $regionCollection;
    protected $_encryptor;
    protected $url;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Directory\Model\RegionFactory $regionCollection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_encryptor = $encryptor;
        $this->regionCollection = $regionCollection;

    }

    /**
     * This function is used for auto validate address true in consignment management page
     * @param  \Magento\Framework\Event\Observer $observer
     * @return Void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $order = $observer->getEvent()->getOrder();           
        $address = $order->getShippingAddress();
        if ($address->getCountryId() != 'AU') return;      
        $suburb = ucwords($address->getCity());
        $regionId = $address->getRegionId();
        $region = $this->regionCollection->create()->load($regionId);
        $state = $region->getCode();
        $postcode = $address->getPostcode();
        $Accountnumber = $this->_encryptor->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/accountNo', $storeScope));
        $userName = $this->_encryptor->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/apiKey', $storeScope));
        $password = $this->_encryptor->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/password', $storeScope));
        if ($this->scopeConfig->getValue('carriers/ausposteParcel/operationMode', $storeScope) == 3) {
            $this->url = 'https://digitalapi.auspost.com.au/test/shipping/v1/address?suburb=' . urlencode($suburb) . '&state=' . $state . '&postcode=' . $postcode;
        } elseif ($this->scopeConfig->getValue('carriers/ausposteParcel/operationMode', $storeScope) == 1) {
            $this->url = 'https://digitalapi.auspost.com.au/shipping/v1/address?suburb=' . urlencode($suburb) . '&state=' . $state . '&postcode=' . $postcode;
        }
        $auspostPartnerId = $this->scopeConfig->getValue('carriers/ausposteParcel/partner_id');
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_USERPWD, $userName . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $Accountnumber));
        $result = curl_exec($ch);
        $responseData = json_decode($result);
        if (isset($responseData->found) && sizeof($responseData->results) && in_array(strtolower($suburb), array_map('strtolower', $responseData->results))) {
            $order->setIsAddressValid(1);
            $order->save();
        }
    }
}
