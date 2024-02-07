<?php

namespace Biztech\Ausposteparcel\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Standalone extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ACCOUNT_NO = 'carriers/ausposteParcel/accountNo';
    const XML_PATH_API_KEY = 'carriers/ausposteParcel/apiKey';
    const XML_PATH_PASSWORD = 'carriers/ausposteParcel/password';
    const XML_PATH_OPERATION_MODE = 'carriers/ausposteParcel/operationMode';
    const XML_PATH_ENABLED = 'carriers/ausposteParcel/active';
    const MAX_ADDRESS_LINE = 40;
    protected $_dataHelper;
    protected $scopeConfig;
    protected $_objectManager;
    protected $_encryptorInterface;
    protected $consignmentmodel;
    protected $_fileSystem;
    protected $_storeManager;
    protected $consignmentCollectionFactory;
    protected $ausposteParcelLabelCollectionFactory;

    public function __construct(
        Context $context, 
        \Biztech\Ausposteparcel\Helper\Data $dataHelper, 
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Biztech\Ausposteparcel\Model\Cresource\Consignment\CollectionFactory $consignmentCollectionFactory,
        \Biztech\Ausposteparcel\Model\Cresource\Auspostlabel\CollectionFactory $ausposteParcelLabelCollectionFactory
    ) {
        parent::__construct($context);
        $this->_dataHelper = $dataHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectmanager;
        $this->_encryptorInterface = $encryptorInterface;
        $this->_fileSystem = $fileSystem;
        $this->_storeManager = $storeManager;
        $this->consignmentmodel = $consignmentmodel;
        $this->consignmentCollectionFactory = $consignmentCollectionFactory;
        $this->ausposteParcelLabelCollectionFactory = $ausposteParcelLabelCollectionFactory;
    }
    
    /* Get auspost eparcel account details */
    public function getAuspostDetail() {
        $accountData = array();

        $accountData['account_number'] = $this->_encryptorInterface->decrypt($this->scopeConfig->getValue(self::XML_PATH_ACCOUNT_NO, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        $accountData['username'] = $this->_encryptorInterface->decrypt($this->scopeConfig->getValue(self::XML_PATH_API_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        $accountData['password'] = $this->_encryptorInterface->decrypt($this->scopeConfig->getValue(self::XML_PATH_PASSWORD, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        $accountData['operation_mode'] = $this->scopeConfig->getValue(self::XML_PATH_OPERATION_MODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $accountData;
    }

    /*get auspost 'biztech' partener id*/
    public function getAuspostPartnerId() {
        return $this->scopeConfig->getValue('carriers/ausposteParcel/partner_id');
    }

    /*Check module Activation*/
    public function isActive() {
        $isActive = $this->_dataHelper->getAllWebsites();
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!empty($isActive) && $isEnabled) {
            return true;
        } else return false;
    }

    /* Check Australia post account detail*/
    public function AuspostAccountCheck()
    {
        $accountDetail = $this->getAuspostDetail();
        $accountNo = $accountDetail['account_number'];
        $username = $accountDetail['username'];
        $password = $accountDetail['password'];
        if ($accountDetail['operation_mode'] == '2') {
            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/accounts/' . $accountNo;
        } elseif ($accountDetail['operation_mode'] == '1') {
            $url = 'https://digitalapi.auspost.com.au/shipping/v1/accounts/' . $accountNo;
        } elseif ($accountDetail['operation_mode'] == '3') {
            $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/accounts/' . $accountNo;
        }
        $auspostPartnerId = $this->getAuspostPartnerId();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $accountNo));
        $result = curl_exec($ch);
        $data = json_decode($result);

        if(!empty($data)) {
            if (isset($data->errors) && !empty($data->errors)) {
                $error = array();
                $error['error'] = $data->errors[0];
                return json_encode($error);
            } else {
                return json_encode($data->postage_products);
            }
        } else {
            $error = array();
            $error['error']['message'] = "Something went wrong while saving AusPost detail.";
            return json_encode($error);
        }
    }

    /*Get Auspost Domestic rates*/
    public function getAuspostDomesticRates($shippingdata)
    {
        $accountDetail = $this->getAuspostDetail();
        $accountNo = $accountDetail['account_number'];
        $username = $accountDetail['username'];
        $password = $accountDetail['password'];

        if ($accountDetail['operation_mode'] == '2') {
            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/prices/items';
        } elseif ($accountDetail['operation_mode'] == '1') {
            $url = 'https://digitalapi.auspost.com.au/shipping/v1/prices/shipments';
        } elseif ($accountDetail['operation_mode'] == '3') {
            $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/prices/shipments';
        }
        $auspostPartnerId = $this->getAuspostPartnerId();
        $shippingdata = json_encode($shippingdata);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $shippingdata);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $accountNo, 'Content-Length: ' . strlen($shippingdata)));
        $apiResult = curl_exec($ch);
        return $apiResult;
    }

    /*Get Auspost International rates*/
    public function getAuspostInternationalRates($shippingdata)
    {
        $accountDetail = $this->getAuspostDetail();
        $accountNo = $accountDetail['account_number'];
        $username = $accountDetail['username'];
        $password = $accountDetail['password'];

        if ($accountDetail['operation_mode'] == '2') {
            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/prices/items';
        } elseif ($accountDetail['operation_mode'] == '1') {
            $url = 'https://digitalapi.auspost.com.au/shipping/v1/prices/items';
        } elseif ($accountDetail['operation_mode'] == '3') {
            $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/prices/items';
        }
        $auspostPartnerId = $this->getAuspostPartnerId();
        $shippingdata = json_encode($shippingdata);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $shippingdata);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $accountNo, 'Content-Length: ' . strlen($shippingdata)));
        $apiResult = curl_exec($ch);
        return $apiResult;
    }

    /*Create Australia post shipment*/
    public function saveConsignmentData($consignmentData)
    {
        $response = array();
        if(!$this->isActive()) {
            $response['status'] = 'error';
            $response['message'][0]['message'] = __("Extension- Australia Post Parcel Send is not enabled. Please enable it from <b> Store → Configuration → Sales → Shipping Methods → Appjetty Australia Post Parcel Send </b> to generate Auspost shipment");
            return json_encode($response);
        }
        $consignmentData = (array) json_decode($consignmentData);
        if (!isset($consignmentData['returnLabels'])) {
            $consignmentData['returnLabels'] = 0;
        }
        $customer_data = $this->getCustomerData();
        $requestData = $this->createShipmentRequest($consignmentData, $customer_data, $consignmentData['country_id'], $consignmentData['returnLabels']);
        $response = $this->createShipmentReqeustToeParcel($requestData, $consignmentData, $consignmentData['shipping_method'], $customer_data, $consignmentData['country_id']);
        $response = json_encode($response);
        return $response;
    }

    /*Get Customer data*/
    public function getCustomerData() {
        $globalData = array();

        /* account details */
        $accountDetail = $this->getAuspostDetail();
        $globalData['account_number'] = $accountDetail['account_number'];
        $globalData['username'] = $accountDetail['username'];
        $globalData['password'] = $accountDetail['password'];
        $globalData['operation_mode'] = $accountDetail['operation_mode'];

        /* return address details */
        $globalData['returnAddressName'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $globalData['returnAddressLine1'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressLine1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $globalData['returnAddressLine2'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressLine2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $globalData['returnAddressLine3'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressLine3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $globalData['returnAddressLine4'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressLine4', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $globalData['returnCountryCode'] = 'AU';
        $globalData['returnPostcode'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressPostcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $globalData['returnStateCode'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressStateCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $globalData['returnSuburb'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressSuburb', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $globalData['returnAddressPhone'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressPhone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $globalData['returnAddressEmail'] = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressEmail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        /* label configuration details */
        $globalData['type'] = 'PRINT';
        $parcelLayout = $this->scopeConfig->getValue('carriers/ausposteParcel/labelParcelLayout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $expressLayout = $this->scopeConfig->getValue('carriers/ausposteParcel/labelExpressLayout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $internationalLayout = $this->scopeConfig->getValue('carriers/ausposteParcel/labelParcelLayoutInternational', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $playout = '';
        $explayout = '';
        $intlLayout = '';

        // parcel label layout
        if ($parcelLayout == 1) {
            $playout = 'A4-4pp';
        } elseif ($parcelLayout == 2) {
            $playout = 'A4-1pp';
        } elseif ($parcelLayout == 3) {
            $playout = 'THERMAL-LABEL-A6-1PP';
        }

        // express label layout
        if ($expressLayout == 1) {
            $explayout = 'A4-3pp';
        } elseif ($expressLayout == 2) {
            $explayout = 'A4-1pp';
        } elseif ($expressLayout == 3) {
            $explayout = 'THERMAL-LABEL-A6-1PP';
        }
        
        // international label layout
        if ($internationalLayout == 1) {
            $intlLayout = 'A4-4pp';
        } elseif ($internationalLayout == 2) {
            $intlLayout = 'A4-1pp';
        } elseif ($internationalLayout == 3) {
            $intlLayout = 'THERMAL-LABEL-A6-1PP';
        }

        $globalData['label_layout_parcel'] = $playout;
        $globalData['label_layout_express'] = $explayout;
        $globalData['auspost_branding'] = $this->scopeConfig->getValue('carriers/ausposteParcel/auspostBranding', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $globalData['left_offset'] = $this->scopeConfig->getValue('carriers/ausposteParcel/leftOffest', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('carriers/ausposteParcel/leftOffest', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : 0;
        $globalData['top_offset'] = $this->scopeConfig->getValue('carriers/ausposteParcel/topOffset', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('carriers/ausposteParcel/topOffset', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : 0;
        $globalData['lable_parcel_layout_international'] = $intlLayout;
        $globalData['commercialvalue'] = $this->scopeConfig->getValue('carriers/ausposteParcel/commercialvalue', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $globalData['description_of_other'] = $this->scopeConfig->getValue('carriers/ausposteParcel/description_of_other', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('carriers/ausposteParcel/description_of_other', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : "";
        $globalData['article_discription'] = $this->scopeConfig->getValue('carriers/ausposteParcel/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $globalData['classification_type'] = $this->scopeConfig->getValue('carriers/ausposteParcel/classificationtype', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $globalData;
    }

    /*Create shipment requesr data*/
    public function createShipmentRequest($consignment_result, $customer_data, $country, $returnlabels)
    {
        $requestData = array();
        $requestData['shipments'] = array();

        $requestData['shipments'][0]['shipment_reference'] = $consignment_result['consignment_number'];
        if (isset($consignment_result['sender_references']) && $consignment_result['sender_references'] != null) {
            $requestData['shipments'][0]['sender_references'] = $consignment_result['sender_references'];
        }

        // Sender Address
        $requestData['shipments'][0]['from']['name'] = $customer_data['returnAddressName'];
        $requestData['shipments'][0]['from']['lines'][] = $customer_data['returnAddressLine1'];
        $requestData['shipments'][0]['from']['lines'][] = $customer_data['returnAddressLine2'];
        $requestData['shipments'][0]['from']['lines'][] = $customer_data['returnAddressLine3'];
        $requestData['shipments'][0]['from']['suburb'] = $customer_data['returnSuburb'];
        $requestData['shipments'][0]['from']['state'] = $customer_data['returnStateCode'];
        $requestData['shipments'][0]['from']['postcode'] = $customer_data['returnPostcode'];
        //$requestData['shipments'][0]['from']['phone'] = $customer_data['returnAddressPhone'];
        $requestData['shipments'][0]['from']['email'] = $customer_data['returnAddressEmail'];
        if ($country != 'AU') {
            $requestData['shipments'][0]['to']['country'] = $customer_data['returnCountryCode'];
        }
        // Receiver Address
        $requestData['shipments'][0]['to']['name'] = $consignment_result['firstname'] . ' ' . $consignment_result['lastname'];
        $requestData['shipments'][0]['to']['business_name'] = isset($consignment_result['company']) ? $consignment_result['company'] : '';

        $street = explode("\n", $consignment_result['street']);
        foreach($street as $key => $line) {
            if (strlen($line) > self::MAX_ADDRESS_LINE) {
                $requestData['shipments'][0]['to']['lines'][] = substr($line, 0, self::MAX_ADDRESS_LINE);
            } else {
                $requestData['shipments'][0]['to']['lines'][] = $line;
            }
        }

        $requestData['shipments'][0]['to']['suburb'] = $consignment_result['city'];
        if ($country != 'AU') {
            $requestData['shipments'][0]['to']['country'] = $consignment_result['country_id'];
        } else {
            $requestData['shipments'][0]['to']['state'] = $consignment_result['region'];
        }
        $requestData['shipments'][0]['to']['postcode'] = $consignment_result['postcode'];
        $requestData['shipments'][0]['to']['phone'] = str_pad($consignment_result['telephone'], 10, '0', STR_PAD_LEFT);
        $requestData['shipments'][0]['to']['email'] = $consignment_result['email'];
        $requestData['shipments'][0]['to']['delivery_instructions'] = $consignment_result['delivery_instructions'];

        // Items Data
        $index = 0;
        $article_data = $consignment_result['articleData'];
        foreach ($article_data as $article) {
            $item = (array) $article;
            $requestData['shipments'][0]['items'][$index]['item_reference'] = $item['article_number'];
            $requestData['shipments'][0]['items'][$index]['product_id'] = $consignment_result['product_id'];
            if ($returnlabels == 1) {
                $requestData['shipments'][0]['movement_type'] = 'RETURN';
            } else {
                $requestData['shipments'][0]['items'][$index]['length'] = number_format($item['length'], 1);
                $requestData['shipments'][0]['items'][$index]['height'] = number_format($item['height'], 1);
                $requestData['shipments'][0]['items'][$index]['width'] = number_format($item['width'], 1);
                $requestData['shipments'][0]['items'][$index]['weight'] = number_format($item['actual_weight'], 2);
            }
            if (isset($item['item_id']) && $item['item_id'] != null) {
                $requestData['shipments'][0]['items'][$index]['item_id'] = $item['item_id'];
            }
            if ($country != 'AU') {
                $requestData['shipments'][0]['items'][$index]['item_contents']['description'] = $customer_data['article_discription'];
                $requestData['shipments'][0]['items'][$index]['item_contents']['quantity'] = 1;
                if (isset($item['unit_value']) && $item['unit_value'] != null) {
                    $requestData['shipments'][0]['items'][$index]['item_contents']['value'] = $item['unit_value'];
                } else {
                    $requestData['shipments'][0]['items'][$index]['item_contents']['value'] = 1.00;
                }
                $requestData['shipments'][0]['items'][$index]['item_contents']['tariff_code'] = 71131900;
                $requestData['shipments'][0]['items'][$index]['item_contents']['country_of_origin'] = "AU";

                $requestData['shipments'][0]['items'][$index]['commercial_value'] = ($customer_data['commercialvalue'] == 1) ? 'true' : 'false';
                if ($customer_data['commercialvalue'] == 1) {
                    $requestData['shipments'][0]['items'][$index]['classification_type'] = 'OTHER';
                    $requestData['shipments'][0]['items'][$index]['description_of_other'] = $customer_data['description_of_other'];
                } else {
                    $requestData['shipments'][0]['items'][$index]['classification_type'] = $customer_data['classification_type'];
                }
            } else {
                $requestData['shipments'][0]['items'][$index]['allow_partial_delivery'] = ($consignment_result['partial_delivery_allowed'] == 1) ? 'true' : 'false';
                $requestData['shipments'][0]['items'][$index]['authority_to_leave'] = ($consignment_result['delivery_signature_allowed'] == 1) ? 'false' : 'true';
                /*safe_drop_enabled and TRANSIT_COVER field added in Shipment creat API : Apr-2021 - By JH*/
                $requestData['shipments'][0]['items'][$index]['safe_drop_enabled'] = ($consignment_result['safeDropEnabled'] == 1) ? 'true' : 'false';
                if($item['is_transit_cover_required']=='Y') {
                    $requestData['shipments'][0]['items'][$index]['features']['TRANSIT_COVER']['attributes']['cover_amount'] = $item['transit_cover_amount'];
                }
                $requestData['shipments'][0]['items'][$index]['contains_dangerous_goods'] = ($consignment_result['contains_dangerous_goods'] == 1) ? 'true' : 'false';
                if($consignment_result['contains_dangerous_goods']==1) {
                    $requestData['shipments'][0]['items'][$index]['transportable_by_air'] = ($consignment_result['transportable_by_air'] == 1) ? 'true' : 'false';
                    $requestData['shipments'][0]['items'][$index]['dangerous_goods_declaration'] = $consignment_result['dangerous_goods_declaration'];
                }
            }
            $index++;
        }
        $requestData['shipments'][0]['email_tracking_enabled'] = ($consignment_result['email_notification'] == 1) ? 'true' : 'false';
        return $requestData;
    }

    /*Generate Austtralia post shipment*/
    public function createShipmentReqeustToeParcel($shipmentData, $consignment_result, $shippingMethod, $customer_detail, $country)
    {
        $response = array();
        $shipment_data = json_encode($shipmentData);
        $account_no = $customer_detail["account_number"];
        $username = $customer_detail["username"];
        $password = $customer_detail["password"];
        if ($customer_detail['operation_mode'] == '2') {
            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/shipments';
        } elseif ($customer_detail['operation_mode'] == '1') {
            $url = 'https://digitalapi.auspost.com.au/shipping/v1/shipments';
        } elseif ($customer_detail['operation_mode'] == '3') {
            $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/shipments';
        }
        $auspostPartnerId = $this->getAuspostPartnerId();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $shipment_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $account_no, 'Content-Length: ' . strlen($shipment_data)));
        $result = curl_exec($ch);
        $data = json_decode($result);
        if (isset($data->errors[0]->code) && $data->errors[0]->code) {
            $response['status'] = 'error';
            $response['message'] = $data->errors;
        } else {
            if(!isset($data->shipments)) {
                $response['status'] = 'error';
                 $response['message'][0]['message'] = "Semething went wrong while submitting consignment.";
            } else {
                $response['shipments'] = $data->shipments;
                foreach ($data->shipments as $key => $value) {
                    $shipmentId = $value->shipment_id;
                    $response['labels'] = $this->GetLabelsReqeustToeParcel($shipmentId, $consignment_result, $shippingMethod, $customer_detail, $country);
                }
            }
        }
        return $response;
    }

    /*Create Auspost label request*/
    public function GetLabelsReqeustToeParcel($shipmentId, $consignment_result, $shippingMethod, $customer_detail, $country)
    {
        $account_no = $customer_detail["account_number"];
        $username = $customer_detail["username"];
        $password = $customer_detail["password"];

        $label_data['preferences'][0]['type'] = 'PRINT';

        /* NK : For Marketplace */
        $shippingMethodlower = strtolower($shippingMethod);
        $parcel = strpos($shippingMethodlower, 'parcel');
        $express = strpos($shippingMethodlower, 'express');
        $group = $consignment_result['label_group'];

        if ($country != 'AU') {
            $layout = $customer_detail['lable_parcel_layout_international'];
            $label_data['preferences'][0]['groups'][0]['branded'] = true; // for international we always have to pass true.
        } else {
            if ($parcel) {
                $layout = $customer_detail['label_layout_parcel'];
            } elseif ($express) {
                $layout = $customer_detail['label_layout_express'];
            }
            $label_data['preferences'][0]['groups'][0]['branded'] = $customer_detail['auspost_branding'] ? 'true' : 'false';
        }

        $label_data['preferences'][0]['groups'][0]['group'] = $group;
        $label_data['preferences'][0]['groups'][0]['layout'] = $layout;
        $label_data['preferences'][0]['groups'][0]['left_offset'] = $customer_detail['left_offset'];
        $label_data['preferences'][0]['groups'][0]['top_offset'] = $customer_detail['top_offset'];
        $label_data['shipments'][0]['shipment_id'] = $shipmentId;
        $labelData = json_encode($label_data);
        if ($customer_detail['operation_mode'] == '2') {
            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/labels';
        } elseif ($customer_detail['operation_mode'] == '1') {
            $url = 'https://digitalapi.auspost.com.au/shipping/v1/labels';
        } elseif ($customer_detail['operation_mode'] == '3') {
            $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/labels';
        }
        $auspostPartnerId = $this->getAuspostPartnerId();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $labelData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $account_no, 'Content-Length: ' . strlen($labelData)));
        $result = curl_exec($ch);
        $data = json_decode($result);
        $resultError = array();
        if (isset($data->errors[0]->code) && $data->errors[0]->code) {
            $resultError['status'] = 'error';
            $resultError['message'] = $data->errors;
            return $resultError;
        } else {
            $data_array = (array) $data;
            return $data_array['labels'];
        }
    }

    /*Get Australia post Label*/
    public function downloadLabel($requestData)
    {
        $response = array();
        if(!$this->isActive()) {
            $response['status'] = 'error';
            $response['message'] = __("Extension- Australia Post Parcel Send is not enabled. Please enable it from <b> Store → Configuration → Sales → Shipping Methods → Appjetty Australia Post Parcel Send </b>");
            return json_encode($response);
        }
        $requestData = (array) json_decode($requestData);
        if (isset($requestData['return_label']) && $requestData['return_label'] == 1) {
            $response = $this->changelablerequest($requestData);
        } else {
            $response = $this->defaultlablerequest($requestData);
        }
        return json_encode($response);
    }

    /*Generate Australia post return label*/
    public function changelablerequest($requestData)
    {
        $response = array();
        $consignments = $this->consignmentmodel->load($requestData['consignment_number'], 'consignment_number');
        if (isset($consignments['return_lable_url']) && !is_null($consignments['return_lable_url']) && $consignments['return_lable_url'] != '' && file_exists($consignments['return_lable_url'])) {
            $response['status'] = 'success';
            $response['message'] = 'URL Exist';
            $response['pdf_url'] = $consignments['return_lable_url'];
        } else {
            $account_detail = $this->getAuspostDetail();
            $account_no = $account_detail["account_number"];
            $username = $account_detail["username"];
            $password = $account_detail["password"];
            if ($account_detail['operation_mode'] == '2') {
                //$url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/labels/' . $requestData['request_id'];
                $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/shipments?shipment_ids=' . $requestData['request_id'];
            /* $error['status'] = 'error';
              $error['message'] = 'You can not generate "Return Labels" using test credentials';
              $error = json_encode($error);
              echo $error;exit; */
            } elseif ($account_detail['operation_mode'] == '1') {
                $url = 'https://digitalapi.auspost.com.au/shipping/v1/shipments?shipment_ids=' . $requestData['request_id'];
            } elseif ($account_detail['operation_mode'] == '3') {
                $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/shipments?shipment_ids=' . $requestData['request_id'];
            }
            $auspostPartnerId = $this->getAuspostPartnerId();

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $account_no));
            $result = curl_exec($ch);
            $data = json_decode($result);
            $mediaPath = $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
            $bizDir ='biztech/eParcelReturnLabel';
            $labelDir = $mediaPath . $bizDir;
            if (!is_dir($labelDir)) {
                mkdir($labelDir, 0777, true);
            }
            if (isset($data->errors[0]->code) && $data->errors[0]->code) {
                $response['status'] = 'error';
                $response['message'] = $data->errors;
            } else {
                $filename = $data->shipments[0]->items[0]->tracking_details->consignment_id;
                $dirPath = $labelDir . "/" . $filename . ".pdf";
                $status = $data->shipments[0]->items[0]->label->status;
                if ($status == 'Available') {
                    $labelPath = $data->shipments[0]->items[0]->label->label_url;
                    $pdfContent = $this->get_web_page($labelPath);
                    $success = file_put_contents($dirPath, $pdfContent);
                    $dirPath1 = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $bizDir . "/" . $filename . ".pdf";
                    $response['status'] = 'success';
                    $response['pdf_url'] = $dirPath1;
                    if ($success) {
                        if (isset($consignments['consignment_number'])) {
                            $label_date = date("Y-m-d H:i:s", strtotime($data->shipments[0]->items[0]->label->label_creation_date));
                           $this->consignmentmodel->load($consignments['consignment_number'], 'consignment_number')
                                    ->setReturnLableUrl($dirPath1)
                                    ->setReturnLabelCreationDate($label_date)
                                    ->save();
                        }  else {
                            $response['status'] = 'error';
                            $response['message'] = __('Consignment data not found.');
                       }
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = __('Currentlly label is not Available.');
                }
            }
        }
        return $response;
    }

    /*Auspost label generate*/
    public function defaultlablerequest($requestData)
    {
        $response = array();
        $consignments = $this->consignmentmodel->load($requestData['consignment_number'], 'consignment_number');
        if (isset($consignments['label_url']) && !is_null($consignments['label_url']) && $consignments['label_url'] != '' && file_exists($consignments['label_url'])) {
            $response['status'] = 'success';
            $response['message'] = 'URL Exist';
            $response['pdf_url'] = $consignments['label_url'];
        } else {
            $account_detail = $this->getAuspostDetail();
            $account_no = $account_detail["account_number"];
            $username = $account_detail["username"];
            $password = $account_detail["password"];

            if ($account_detail['operation_mode'] == '2') {
                $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/labels/' . $requestData['request_id'];
            } elseif ($account_detail['operation_mode'] == '1') {
                $url = 'https://digitalapi.auspost.com.au/shipping/v1/labels/' . $requestData['request_id'];
            } elseif ($account_detail['operation_mode'] == '3') {
                $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/labels/' . $requestData['request_id'];
            }
            $auspostPartnerId = $this->getAuspostPartnerId();

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $account_no));
            $result = curl_exec($ch);
            $data = json_decode($result);
            $mediaPath = $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
            $bizDir ='biztech/eParcelLabel';
            $labelDir = $mediaPath . $bizDir;
            if (!is_dir($labelDir)) {
                mkdir($labelDir, 0777, true);
            }
            if (isset($data->errors[0]->code) && $data->errors[0]->code) {
                $response['status'] = 'error';
                $response['message'] = $data->errors;
            } else {
                $dirPath =  $labelDir .'/'. $requestData['eparcel_consignment_id'] . ".pdf";
                if (!isset($data->labels)) {
                    $response['status'] = 'error';
                    $response['message'] = __('Please generate the labels');
                    return $response;
                }
                if ($data->labels[0]->status == 'AVAILABLE') {
                    $labelPath = $data->labels[0]->url;
                    $pdfContent = $this->get_web_page($labelPath);
                    $success = file_put_contents($dirPath, $pdfContent);
                    $dirPath1 = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $bizDir . '/' . $requestData['eparcel_consignment_id'] . ".pdf";
                    $response['status'] = 'success';
                    $response['pdf_url'] = $dirPath1;
                    if ($success) {
                        if (isset($consignments['consignment_number'])) {
                            $label_date = date("Y-m-d H:i:s", strtotime($data->labels[0]->request_date));
                            $this->consignmentmodel->load($consignments['consignment_number'], 'consignment_number')
                                    ->setLabelUrl($dirPath1)
                                    ->setLabelCreationDate($label_date)
                                    ->save();
                       } else {
                            $response['status'] = 'error';
                            $response['message'] = __('Consignment data not found.');
                       }
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = __('Currentlly label is not Available.');
                }
            }
        }
        return $response;
    }

    public function get_web_page($url)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_USERAGENT => "spider", // who am i
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
        );
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /* Mass Generate Australia post shipment*/
    public function saveMultipleConsignmentData($consignmentData)
    {
        $response = array();
        if(!$this->isActive ()) {
            $response['status'] = 'error';
            $response['message'][0]['message'] = __("Extension- Australia Post Parcel Send is not enabled. Please enable it from <b> Store → Configuration → Sales → Shipping Methods → Appjetty Australia Post Parcel Send </b>");
            return json_encode($response);
        }
        $consignmentData = (array) json_decode($consignmentData);
        $i = 0;
        $count = 0;
        $consignment_result = array();
        foreach ($consignmentData as $data) {
            $data = (array) $data;
            if (!isset($data['returnLabels'])) {
                $data['returnLabels'] = 0;
            }
            $article_data = $data['articleData']; 
            $consignment_result[$count] = $data;
            $customer_data = $this->getCustomerData();
            $requestData[$i] = $this->createMultipleShipmentRequest($consignment_result[$count], $article_data, $customer_data, $consignment_result[$count]['country_id'], $data['returnLabels']);
            $i++;
            $count++;
        }
        $consignmentData = array();
        $consignmentData = $consignment_result;
        $requestData1 = array();
        $requestData1['shipments'] = $requestData;
        $response = $this->createMultipleShipmentReqeustToeParcel($requestData1, $consignmentData, $customer_data);
        $response = json_encode($response);
        return $response;
            
    }
    public function createMultipleShipmentRequest($consignment_result, $article_data, $customer_data, $country, $returnLabels)
    {
        if ($returnLabels == 0) {
            $return = $this->createMultipleShipmentReqeustlable($consignment_result, $article_data, $customer_data, $country, $returnLabels);
            return $return;
        }
    }

    public function createMultipleShipmentReqeustlable($consignment_result, $article_data, $customer_data, $country, $returnLabels)
    {
        $requestData['shipment_reference'] = $consignment_result['consignment_number'];
        if (isset($consignment_result['sender_references']) && $consignment_result['sender_references'] != null) {
            $requestData['sender_references'] = $consignment_result['sender_references'];
        }

        // Sender Address
        $requestData['from']['name'] = $customer_data['returnAddressName'];
        $requestData['from']['lines'][] = $customer_data['returnAddressLine1'];
        $requestData['from']['lines'][] = $customer_data['returnAddressLine2'];
        $requestData['from']['lines'][] = $customer_data['returnAddressLine3'];
        $requestData['from']['suburb'] = $customer_data['returnSuburb'];
        $requestData['from']['state'] = $customer_data['returnStateCode'];
        $requestData['from']['postcode'] = $customer_data['returnPostcode'];
        //$requestData['shipments'][0]['from']['phone'] = $customer_data['returnAddressPhone'];
        $requestData['from']['email'] = $customer_data['returnAddressEmail'];
        if ($country != 'AU') {
            $requestData['to']['country'] = $customer_data['returnCountryCode'];
        }
        // Receiver Address
        $requestData['to']['name'] = $consignment_result['firstname'] . ' ' . $consignment_result['lastname'];
        $requestData['to']['business_name'] = isset($consignment_result['company']) ? $consignment_result['company'] : '';

        $street = explode("\n", $consignment_result['street']);
        foreach($street as $key => $line) {
            if (strlen($line) > self::MAX_ADDRESS_LINE) {
                $requestData['to']['lines'][] = substr($line, 0, self::MAX_ADDRESS_LINE);
            } else {
                $requestData['to']['lines'][] = $line;
            }
        }
        
        $requestData['to']['suburb'] = $consignment_result['city'];
        if ($country != 'AU') {
            $requestData['to']['country'] = $consignment_result['country_id'];
        } else {
            $requestData['to']['state'] = $consignment_result['region'];
        }
        $requestData['to']['postcode'] = $consignment_result['postcode'];
        $requestData['to']['phone'] = str_pad($consignment_result['telephone'], 10, '0', STR_PAD_LEFT);
        $requestData['to']['email'] = $consignment_result['email'];
        $requestData['to']['delivery_instructions'] = $consignment_result['delivery_instructions'];

        // Items Data
        $index = 0;
        foreach ($article_data as $article) {
            $item = (array) $article;
            $requestData['items'][$index]['item_reference'] = $item['article_number'];
            $requestData['items'][$index]['product_id'] = $consignment_result['product_id'];
            $requestData['items'][$index]['length'] = number_format($item['length'], 1);
            $requestData['items'][$index]['height'] = number_format($item['height'], 1);
            $requestData['items'][$index]['width'] = number_format($item['width'], 1);
            $requestData['items'][$index]['weight'] = number_format($item['actual_weight'], 2);
            if ($country != 'AU') {
                $requestData['items'][$index]['item_contents']['description'] = $customer_data['article_discription'];
                $requestData['items'][$index]['item_contents']['quantity'] = 1;
                $requestData['items'][$index]['item_contents']['value'] = 1.00;
                $requestData['items'][$index]['item_contents']['tariff_code'] = 71131900;
                $requestData['items'][$index]['item_contents']['country_of_origin'] = "AU";

                $requestData['items'][$index]['commercial_value'] = ($customer_data['commercialvalue'] == 1) ? 'true' : 'false';
                if ($customer_data['commercialvalue'] == 1) {
                    $requestData['items'][$index]['classification_type'] = 'OTHER';
                    $requestData['items'][$index]['description_of_other'] = $customer_data['description_of_other'];
                } else {
                    $requestData['items'][$index]['classification_type'] = $customer_data['classification_type'];
                }
            } else {
                $requestData['items'][$index]['allow_partial_delivery'] = ($consignment_result['partial_delivery_allowed'] == 1) ? 'true' : 'false';
                $requestData['items'][$index]['authority_to_leave'] = ($consignment_result['delivery_signature_allowed'] == 1) ? 'false' : 'true';
                /*safe_drop_enabled and TRANSIT_COVER field added in Shipment creat API : Apr-2021 - By JH*/
                $requestData['items'][$index]['safe_drop_enabled'] = ($consignment_result['safeDropEnabled'] == 1) ? 'true' : 'false';
                if($item['is_transit_cover_required']=='Y') {
                    $requestData['items'][$index]['features']['TRANSIT_COVER']['attributes']['cover_amount'] = $item['transit_cover_amount'];
                }
                $requestData['items'][$index]['contains_dangerous_goods'] = ($consignment_result['contains_dangerous_goods'] == 1) ? 'true' : 'false';
                if($consignment_result['contains_dangerous_goods']==1) {
                    $requestData['items'][$index]['transportable_by_air'] = ($consignment_result['transportable_by_air'] == 1) ? 'true' : 'false';
                    $requestData['items'][$index]['dangerous_goods_declaration'] = $consignment_result['dangerous_goods_declaration'];
                }
            }
            $index++;
        }
        $requestData['email_tracking_enabled'] = ($consignment_result['email_notification'] == 1) ? 'true' : 'false';
        return $requestData;
    }

    public function createMultipleShipmentReqeustToeParcel($shipmentData, $consignment_result, $customer_detail)
    {
        $response = array();
        $shipment_data = json_encode($shipmentData);
        $account_no = $customer_detail["account_number"];
        $username = $customer_detail["username"];
        $password = $customer_detail["password"];
        if ($customer_detail['operation_mode'] == '2') {
            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/shipments';
        } elseif ($customer_detail['operation_mode'] == '1') {
            $url = 'https://digitalapi.auspost.com.au/shipping/v1/shipments';
        } elseif ($customer_detail['operation_mode'] == '3') {
            $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/shipments';
        }
        $auspostPartnerId = $this->getAuspostPartnerId();
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $shipment_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $account_no, 'Content-Length: ' . strlen($shipment_data)));
        $result = curl_exec($ch);
        $data = json_decode($result);
        if (isset($data->errors[0]->code) && $data->errors[0]->code) {
            $response['status'] = 'error';
            $response['message'] = $data->errors;
        } else {
            if(!isset($data->shipments)) {
                $response['status'] = 'error';
                $response['message'][0]['message'] = "Shipment not generated, Please try after some time.";
            } else {
                $response['shipments'] = $data->shipments;
            }
        }
        return $response;
    }

    /* Mass Generate Australia post label*/
    public function GenerateMultipleLabels($requestData)
    {
        if(!$this->isActive()) {
            $response['status'] = 'error';
            $response['message'] = __("Extension- Australia Post Parcel Send is not enabled. Please enable it from <b> Store → Configuration → Sales → Shipping Methods → Appjetty Australia Post Parcel Send </b>");
            return json_encode($response);
        }
        $response = array();
        if (isset($requestData)) {
            $requestData = (array) json_decode($requestData);
            $customer_data = $this->getCustomerData();
            $account_number = $customer_data['account_number'];
            $data = $requestData['shipment_id'];
            $returnLabels = $requestData['returnLabel'];
            $shipment_array = array();
            $url_array = array();
            $count = 0;
            foreach ($data as $ids) {
                $id = $ids->consignment_id;
                if ($returnLabels == 0) {
                    $consignments_data = $this->consignmentCollectionFactory->create()->addFieldToSelect('label_url')->addFieldToFilter('consignment_number', $id)->load();
                }
                $consignments_data = $consignments_data->getData();
                $label_url = $consignments_data[0]['label_url'];
                if (is_null($label_url) || $label_url == '') {
                    $shipment_array[$count] = $ids;
                    $count++;
                } else {
                    $ids->url = $label_url;
                    $url_array[$count]['consignment_number'] = $ids->consignment_id;
                    $url_array[$count]['label_url'] = $ids->url;
                    $count++;
                }
            }
            if (!empty($shipment_array)) {
                $i = 0;
                $cons_id = array();
                foreach ($shipment_array as $ids) {
                    $cons_id[$i] = $ids->consignment_id;
                    $consignment_data[$i] = $this->consignmentmodel->load($cons_id[$i], 'consignment_number');
                    $consignment_data[$i] = $consignment_data[$i]->getData();
                    $i++;
                }
                $n = 0;
                foreach ($shipment_array as $ids) {
                    $ship[$n]['shipment_id'] = $ids->shipmentId;
                    $n++;
                }
                $request_id = $this->GetMultipleLabelsReqeust($ship, $consignment_data, $customer_data);
                if(isset($request_id['status'])) {
                    if($request_id['status']=='error') {
                        $response['status'] = 'error';
                        $response['Message'] = __($request_id['message']);
                        return json_encode($response);
                    }
                }
                $response_new = array();
                if ($returnLabels == 0) {
                    $response_new = $this->downloadMultipleLabel($request_id, $cons_id, $account_number);
                }
                if(isset($response_new['status'])) {
                    if($response_new['status']=='error') {
                        $response['status'] = 'error';
                        $response['Message'] = __($response_new['message']);
                        return json_encode($response);
                    }
                }
                $response['status'] = 'success';
                $response['request_id'] = $response_new['request_id'];
                $response['url'] = array_merge($response_new['url'], $url_array);
            } else {
                $response['status'] = 'success';
                $response['url'] = $url_array;
                $response['Message'] = "URL Exist";
            }
        } else {
            $response['status'] = 'error';
            $response['Message'] = __("Missing required consignment data.");
        }
        return json_encode($response);
    }

    public function GetMultipleLabelsReqeust($shipmentId, $consignment_result, $customer_detail)
    {
        $account_no = $customer_detail["account_number"];
        $username = $customer_detail["username"];
        $password = $customer_detail["password"];

        $label_data['preferences'][0]['type'] = 'PRINT';
        $i = 0;
        foreach ($consignment_result as $consignment) {
            $orderData = $this->_objectManager->create('Magento\Sales\Model\Order')->load($consignment['order_id']);
            $product_id = explode('-', $orderData->getShippingMethod());
            $auspostLabel = $this->ausposteParcelLabelCollectionFactory->create()->addFieldToFilter('charge_code', $product_id[1])->getData();
            $group = $auspostLabel[0]['label_group'];
            $consignment['label_group'] = $group;
            if ($auspostLabel[0]['label_group'] == null) {
                $consignment['label_group'] = 'Parcel Post';
            }
            $shippingAddress = $orderData->getShippingAddress()->getData();
            $consignment['country_id'] = $shippingAddress['country_id'];
            if ($consignment['country_id'] !== "AU") {
                 $consignment['label_group'] = 'International';
            }
            $shippingDiscription = $orderData->getShippingDescription();
            $group = $consignment['label_group'];
            $parcel = stripos($group, 'parcel');
            $express = stripos($group, 'express');
            if ($consignment['country_id'] != 'AU') {
                $layout = $customer_detail['lable_parcel_layout_international'];
                $data[$i]['branded'] = true; // for international we always have to pass true.
            } else {
                if ($parcel !== false) {
                    $layout = $customer_detail['label_layout_parcel'];
                } elseif ($express !== false) {
                    $layout = $customer_detail['label_layout_express'];
                }
                $data[$i]['branded'] = $customer_detail['auspost_branding'] ? true : false;
            }

            $data[$i]['group'] = $group;
            $data[$i]['layout'] = $layout;
            $data[$i]['left_offset'] = $customer_detail['left_offset'];
            $data[$i]['top_offset'] = $customer_detail['top_offset'];
            $i++;
        }
        $label_data['preferences'][0]['groups'] = $data;
        $label_data['shipments'] = $shipmentId;
        //modification for mass label generate 27/7/21
        $label_data['wait_for_label_url'] = true;
        $labelData = json_encode($label_data);
        if ($customer_detail['operation_mode'] == '2') {
            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/labels';
        } elseif ($customer_detail['operation_mode'] == '1') {
            $url = 'https://digitalapi.auspost.com.au/shipping/v1/labels';
        } elseif ($customer_detail['operation_mode'] == '3') {
            $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/labels';
        }
        $auspostPartnerId = $this->getAuspostPartnerId();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $labelData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $account_no, 'Content-Length: ' . strlen($labelData)));
        $result = curl_exec($ch);
        $data = json_decode($result);
        $resultError = array();
        if (isset($data->errors[0]->code) && $data->errors[0]->code) {
            $resultError['status'] = 'error';
            $resultError['message'] = $data->errors;
            return $resultError;
        } else {
            $data_array = (array) $data;
            $request_id = $data_array['labels'][0]->request_id;
            return $request_id;
        }
    }

    public function downloadMultipleLabel($request_id, $consignment_id, $account_number)
    {
        $_data['consignment_id'] = $consignment_id;
        $_data['account_number'] = $account_number;
        $_data['request_id'] = $request_id;

        $customer_detail = $this->getCustomerData();
        $account_no = $customer_detail["account_number"];
        $username = $customer_detail["username"];
        $password = $customer_detail["password"];
        if ($customer_detail['operation_mode'] == '2') {
            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/labels/' . $_data['request_id'];
        } elseif ($customer_detail['operation_mode'] == '1') {
            $url = 'https://digitalapi.auspost.com.au/shipping/v1/labels/' . $_data['request_id'];
        } elseif ($customer_detail['operation_mode'] == '3') {
            $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/labels/' . $_data['request_id'];
        }
        $auspostPartnerId = $this->getAuspostPartnerId();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $account_no));
        $result = curl_exec($ch);
        $data = json_decode($result);
        if (isset($data->errors[0]->code) && $data->errors[0]->code) {
            $response['status'] = 'error';
            $response['message'] = $data->errors;
        } else {
            $filename = implode("-", $_data['consignment_id']);
            /*Mass label generate issue fix - when file name was too long : Mar-2021 - By JH*/
            if (strlen($filename) >= 251) {
                $filename = substr($filename, 0, 235) . time();
            }
            /*end*/
            $mediaPath = $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
            $bizDir ='biztech/eParcelLabel';
            $labelDir = $mediaPath . $bizDir;
            if (!is_dir($labelDir)) {
                mkdir($labelDir, 0777, true);
            }
            $dirPath = $labelDir .'/'. $filename . ".pdf";
            if ($data->labels[0]->status == 'AVAILABLE') {
                $labelPath = $data->labels[0]->url;
                $pdfContent = $this->get_web_page($labelPath);
                $success = file_put_contents($dirPath, $pdfContent);
                $dirPath1 = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $bizDir .'/'. $filename . ".pdf";
                $response['status'] = 'success';
                $response['pdf_url'] = $dirPath1;
                if ($success) {
                    $i = 0;
                    foreach ($consignment_id as $single) {
                        $pdf_url = array();
                        $consignment = $this->consignmentmodel->load($single, 'consignment_number');
                        if (isset($consignment['consignment_number'])) {
                            $label_date = date("Y-m-d H:i:s", strtotime($data->labels[0]->url_creation_date));
                            $consignment->setLabelUrl($dirPath1)->setLabelCreationDate($label_date)->save();
                            $pdf_url[$i]['consignment_number'] = $single;
                            $pdf_url[$i]['pdf_url'] = $dirPath1;
                            $i++;
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = __('Consignment data not found.');
                            return $response;
                        }
                    }
                    $response['url'] = $pdf_url;
                    $response['request_id'] = $request_id;
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = __('Currentlly labels are not Available.');
            }
        }
        return $response;
    }

    /*Dispatch order to Australia post*/
    public function dispatchOrder($orderData)
    {
        $response = array();
        if(!$this->isActive()) {
            $response['status'] = 'errorDisable';
            $response['message'] = __("Extension- Australia Post Parcel Send is not enabled. Please enable it from <b> Store → Configuration → Sales → Shipping Methods → Appjetty Australia Post Parcel Send </b>");
            return json_encode($response);
        }
        $order_data = $orderData;
        $customer_detail = $this->getCustomerData();
        $account_no = $customer_detail["account_number"];
        $username = $customer_detail["username"];
        $password = $customer_detail["password"];

        if ($customer_detail['operation_mode'] == '2') {
            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/orders';
        } elseif ($customer_detail['operation_mode'] == '1') {
            $url = 'https://digitalapi.auspost.com.au/shipping/v1/orders';
        } elseif ($customer_detail['operation_mode'] == '3') {
            $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/orders';
        }
        $auspostPartnerId = $this->getAuspostPartnerId();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $order_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $account_no, 'Content-Length: ' . strlen($order_data)));
        $result = curl_exec($ch);
        $data = json_decode($result);
        if (isset($data->errors[0]->code)) {
            $resultError['status'] = 'error';
            $resultError['message'] = $data->errors;
            $response = json_encode($resultError);
            return $response;
        } else {
            $orderId = $data->order->order_id;
            if ($customer_detail['operation_mode'] == '2') {
                $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/accounts/' . $account_no . '/orders/' . $orderId . '/summary';
            } elseif ($customer_detail['operation_mode'] == '1') {
                $url = 'https://digitalapi.auspost.com.au/shipping/v1/accounts/' . $account_no . '/orders/' . $orderId . '/summary';
            } elseif ($customer_detail['operation_mode'] == '3') {
                $url = 'https://digitalapi.auspost.com.au/test/shipping/v1/accounts/' . $account_no . '/orders/' . $orderId . '/summary';
            }
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'AUSPOST-PARTNER-ID: ' . $auspostPartnerId, 'Account-Number: ' . $account_no));
            $result = curl_exec($ch);
            if (isset($data1->errors[0]->code)) {
                $resultError['status'] = 'error';
                $resultError['message'] = $data1->errors;
                $response = json_encode($resultError);
                return $response;
            } else {
                $mediaPath = $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                $bizDir ='biztech/eParcelOrderSummary';
                $labelDir = $mediaPath . $bizDir;
                if (!is_dir($labelDir)) {
                    mkdir($labelDir, 0777, true);
                }
                $dirPath = $labelDir . "/order-summary-" . time() . ".pdf";
                $file = file_put_contents($dirPath, $result);
                $dirPath1 = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $bizDir . "/order-summary-" . time() . ".pdf";
                $orderSummary['order_summary'] = $dirPath1;
                $response = $orderSummary;
            }
        }
        return json_encode($response);
    }

    /*Get bulk labels url*/
    public function getBulkLabels($order_ids)
    {
        if(!$this->isActive()) {
            $response['error'] = true;
            $response['message'] = __("Extension- Australia Post Parcel Send is not enabled. Please enable it from <b> Store → Configuration → Sales → Shipping Methods → Appjetty Australia Post Parcel Send </b>");
            return json_encode($response);
        }
        $response = array();
        if (isset($order_ids)) {
            $data = json_decode($order_ids);
            $orderIds = explode(",", $data);
            $consignments_data = $this->consignmentCollectionFactory->create()->addFieldToSelect('*')->addFieldToFilter('order_id', $orderIds)->load();
            $response = array();
            foreach ($consignments_data as $consignment) {
                $consignment = $consignment->getData();
                $response[$consignment['order_id']] = $consignment['label_url'];
            }
        }
       return json_encode($response);
    }
}