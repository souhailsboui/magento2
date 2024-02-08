<?php

namespace Biztech\Ausposteparcel\Model;

if (!defined('BIZTECH_AUSPOSTEPARCEL_URL')) {
    define('BIZTECH_AUSPOSTEPARCEL_URL', 'ws1.linksync.com');
}

class Api extends \Magento\Framework\Model\AbstractModel
{
    public $ausposteParcelLabelFactory;
    public $logger;
    public $scopeConfig;
    public $encryptorInterface;
    private $_objectManager;
    public $ausposteParcelLabelCollectionFactory;
    public $ausposteParcelAuspostlabelFactory;
    public $ausposteParcelInfoHelper;
    public $_ausposteParceStandaloneHelper;
    protected $request;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Biztech\Ausposteparcel\Model\Cresource\AuspostlabelFactory $ausposteParcelLabelFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Biztech\Ausposteparcel\Model\Cresource\Auspostlabel\CollectionFactory $ausposteParcelLabelCollectionFactory,
        \Biztech\Ausposteparcel\Model\AuspostlabelFactory $ausposteParcelAuspostlabelFactory,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Biztech\Ausposteparcel\Helper\Standalone $ausposteParceStandaloneHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ausposteParcelLabelFactory = $ausposteParcelLabelFactory;
        $this->logger = $context->getLogger();
        $this->scopeConfig = $scopeConfig;
        $this->encryptorInterface = $encryptorInterface;
        $this->_objectManager = $objectmanager;
        $this->ausposteParcelLabelCollectionFactory = $ausposteParcelLabelCollectionFactory;
        $this->ausposteParcelAuspostlabelFactory = $ausposteParcelAuspostlabelFactory;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->_ausposteParceStandaloneHelper = $ausposteParceStandaloneHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
    }

    protected function flushAustpostLabel($auspostlabel)
    {
        try {
            foreach ($auspostlabel as $item) {
                $item->delete();
            }
            $auspostLabelModel = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Cresource\Auspostlabel')->changeAutoIncrement();
            //$this->ausposteParcelLabelFactory->create()->changeAutoIncrement();
        } catch (\Exception $e) {
            $this->logger->log(null, $e->getMessage());
        }
    }

    public function seteParcelMerchantDetails()
    {
        $response = $this->_ausposteParceStandaloneHelper->AuspostAccountCheck();
        $response = json_decode($response,true);
        if (!empty($response)) {
            $auspostlabel = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Cresource\Auspostlabel\Collection');
            if (sizeof($auspostlabel) > 0) {
                $this->flushAustpostLabel($auspostlabel);
            }
            if (isset($response['error'])) {
                $responseArray = ['status' => 'error', 'message' => $response['error']['message']];
                return $responseArray;
            }
            foreach ($response as $key => $value) {
                $auspostLabelModel = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Auspostlabel');
                if (!isset($value['group'])) {
                    $labelGroup = 'Express Post';
                } else {
                    $labelGroup = $value['group'];
                }
                $auspostLabelModel->setLabelGroup($labelGroup)
                    ->setChargeCode($value['product_id'])
                    ->setType($value['type'])
                    ->save();
            }
            $responseArray = ['status' => 'success', 'message' => 'data saved success'];
            return $responseArray;
        }
    }

    public function sendConsignment($consignmentId = null, $orderId = null, $returnLabels = 0)
    {
        $consignmentData = [];
        
        $consignmentData = $this->ausposteParcelInfoHelper->getConsignment($orderId, $consignmentId);
    
        $articleData = $this->ausposteParcelInfoHelper->getArticles($orderId, $consignmentId);

        $orderData = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $shippingAddress = $orderData->getShippingAddress()->getData();
        $region = $this->_objectManager->create('Magento\Directory\Model\Region')->load($orderData->getShippingAddress()->getRegionid())->getCode();
        $product_id = explode('-', $orderData->getShippingMethod());

        $auspostLabel = $this->ausposteParcelLabelCollectionFactory->create()->addFieldToFilter('charge_code', $product_id[1])->getData();
        /* consignment Data */

        $consignmentData['firstname'] = $shippingAddress['firstname'];
        $consignmentData['middlename'] = $shippingAddress['middlename'];
        $consignmentData['lastname'] = $shippingAddress['lastname'];
        $consignmentData['street'] = $shippingAddress['street'];
        $consignmentData['city'] = $shippingAddress['city'];
        $consignmentData['region'] = $region;
        $consignmentData['postcode'] = $shippingAddress['postcode'];
        $consignmentData['country_id'] = $shippingAddress['country_id'];
        $consignmentData['telephone'] = $shippingAddress['telephone'];
        $consignmentData['email'] = $shippingAddress['email'];
        if (strlen($shippingAddress['company']) > 40) {
            $consignmentData['company'] = substr($shippingAddress['company'], 0, 40);
        } else {
            $consignmentData['company'] = $shippingAddress['company'];
        }
        $consignmentData['shipping_method'] = $orderData->getShippingMethod();
        $consignmentData['order_id'] = $orderId;
        $consignmentData['articleData'] = $articleData;
        $consignmentData['articleData'][0]['item_reference'] = 'eparcelItem_' . $product_id[1];
        $consignmentData['account_number'] = $this->encryptorInterface->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/accountNo', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        if ($returnLabels == 1) {
            $shippingDiscription = $orderData->getShippingDescription();
            if (stripos($shippingDiscription, 'PARCEL') !== false) {
                $pid = 'PR';
                $group = 'Parcel Post';
            } elseif (stripos($shippingDiscription, 'EXPRESS') !== false) {
                $pid = 'XPR';
                $group = 'Express Post';
            } else {
                $group = $auspostLabel[0]['label_group'];
                $pid = $product_id[1];
                $consignmentData['product_id'] = $pid;
                $consignmentData['label_group'] = $group;
            }
            $consignmentData['product_id'] = $pid;
            $consignmentData['label_group'] = $group;
        } else {
            $group = $auspostLabel[0]['label_group'];
            $consignmentData['product_id'] = $product_id[1];
            $consignmentData['label_group'] = $group;
        }
        if ($auspostLabel[0]['label_group'] == null) {
            $consignmentData['label_group'] = 'Parcel Post';
        }
        if ($consignmentData['country_id'] !== "AU") {
             $consignmentData['label_group'] = 'International';
        }
        $consignmentData['sender_references'] = (string) __("Order Id: ") . $orderData->getIncrementId();
        $consignmentData['returnLabels'] = $returnLabels;
        $consignmentData['safeDropEnabled'] = $this->scopeConfig->getValue('carriers/ausposteParcel/safeDropEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $consignmentData['transportable_by_air'] = $this->scopeConfig->getValue('carriers/ausposteParcel/transportable_by_air', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $consignmentData['dangerous_goods_declaration'] = $this->scopeConfig->getValue('carriers/ausposteParcel/dangerous_goods_declaration', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      
        $consignmentData = json_encode($consignmentData);
        $response = $this->_ausposteParceStandaloneHelper->saveConsignmentData($consignmentData);
        return $response;
    }

    public function orderSummary($orderData)
    {
      $orderData = json_encode($orderData);     
      $response = $this->_ausposteParceStandaloneHelper->dispatchOrder($orderData);
      $this->jsonHelper = $this->_objectManager->get('Magento\Framework\Json\Helper\Data');
      $data = $this->jsonHelper->jsonDecode($response, true);

      if (isset($data['status'])) {
          if ($data['status'] == 'error') {
              $message = $data['message'][0]['message'];
              $responseArray = ['status' => 'error', 'message' => $data['message']];
              return $responseArray;
          } elseif ($data['status'] == 'errorDisable') {
              $responseArray = ['status' => 'errorDisable', 'message' => $data['message']];
              return $responseArray;
          }
      } else {
          $responseArray = ['status' => 'success', 'url' => $data['order_summary']];
          return $responseArray;
      }
    }

    public function generateMagentoShipment($order_id)
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        $increamentId = $order->getData('increment_id');

        $qty = [];
        if ($order->canShip()) {
            $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
            $shipment = $convertOrder->toShipment($order);
            foreach ($order->getAllItems() as $orderItem) {
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qtyShipped = $orderItem->getQtyToShip();
                $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                $shipment->addItem($shipmentItem);
            }
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            try {
                $shipment->save();
                $shipment->getOrder()->save();

                $shipment->save();
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
            $resultSuccess['status'] = 'success';
            return $resultSuccess;
        } else {
            $resultSuccess['status'] = 'success';
            $resultSuccess['message'] = (__('Cannot do shipment for order #' . $increamentId));
            return $resultSuccess;
        }
    }

    public function massShipmentGenerate($consignment_ids, $returnLabels = 0)
    {
        foreach ($consignment_ids as $key => $value) {
            $orderId = $value[0];
            $consignmentId = $value[1];
            $consignmentData[$key] = $this->ausposteParcelInfoHelper->getConsignment($orderId, $consignmentId);
            $articleData[$key] = $this->ausposteParcelInfoHelper->getArticles($orderId, $consignmentId);

            $orderData = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            $shippingAddress = $orderData->getShippingAddress()->getData();
            $region = $this->_objectManager->create('Magento\Directory\Model\Region')->load($orderData->getShippingAddress()->getRegionid())->getCode();
            $product_id = explode('-', $orderData->getShippingMethod());
            $auspostLabel = $this->ausposteParcelLabelCollectionFactory->create()->addFieldToFilter('charge_code', $product_id[1])->getData();

            /* consignment Data */
            $consignmentData[$key]['firstname'] = $shippingAddress['firstname'];
            $consignmentData[$key]['middlename'] = $shippingAddress['middlename'];
            $consignmentData[$key]['lastname'] = $shippingAddress['lastname'];
            $consignmentData[$key]['street'] = $shippingAddress['street'];
            $consignmentData[$key]['city'] = $shippingAddress['city'];
            $consignmentData[$key]['region'] = $region;
            $consignmentData[$key]['postcode'] = $shippingAddress['postcode'];
            $consignmentData[$key]['country_id'] = $shippingAddress['country_id'];
            $consignmentData[$key]['telephone'] = $shippingAddress['telephone'];
            $consignmentData[$key]['email'] = $shippingAddress['email'];
            if (strlen($shippingAddress['company']) > 40) {
                $consignmentData[$key]['company'] = substr($shippingAddress['company'], 0, 40);
            } else {
                $consignmentData[$key]['company'] = $shippingAddress['company'];
            }
            $consignmentData[$key]['shipping_method'] = $orderData->getShippingMethod();

            $consignmentData[$key]['order_id'] = $orderId;

            $consignmentData[$key]['articleData'] = $articleData[$key];
            $consignmentData[$key]['articleData'][0]['item_reference'] = 'eparcelItem_' . $product_id[1];

            if ($returnLabels == 1) {
                $shippingDiscription = $orderData->getShippingDescription();
                if (stripos($shippingDiscription, 'PARCEL') !== false) {
                    $pid = 'PR';
                    $group = 'Parcel Post';
                } elseif (stripos($shippingDiscription, 'EXPRESS') !== false) {
                    $pid = 'XPR';
                    $group = 'Express Post';
                }
                $consignmentData[$key]['product_id'] = $pid;
                $consignmentData[$key]['label_group'] = $group;
                $consignmentData[$key]['returnLabels'] = $returnLabels;
            } else {
                if ($auspostLabel[0]['label_group'] == null) {
                    $group = 'Express Post';
                } else {
                    $group = $auspostLabel[0]['label_group'];
                }
                if ($shippingAddress['country_id'] !== "AU") {
                    $group = "International";
                }
                $consignmentData[$key]['sender_references'] = (string) __("Order Id: ") . $orderData->getIncrementId();
                $consignmentData[$key]['product_id'] = $product_id[1];
                $consignmentData[$key]['label_group'] = $group;
                $consignmentData[$key]['returnLabels'] = $returnLabels;
            }
            $consignmentData[$key]['safeDropEnabled'] = $this->scopeConfig->getValue('carriers/ausposteParcel/safeDropEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $consignmentData[$key]['transportable_by_air'] = $this->scopeConfig->getValue('carriers/ausposteParcel/transportable_by_air', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $consignmentData[$key]['dangerous_goods_declaration'] = $this->scopeConfig->getValue('carriers/ausposteParcel/dangerous_goods_declaration', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        $consignmentData = json_encode($consignmentData);
        $response = $this->_ausposteParceStandaloneHelper->saveMultipleConsignmentData($consignmentData);
        return $response;
    }

    public function massLabelGenerateAndDownload($finalPostData, $returnLabel = 0)
    {
        $finalPostData['returnLabel'] = $returnLabel;
        $requestData = json_encode($finalPostData);
        $response = $this->_ausposteParceStandaloneHelper->GenerateMultipleLabels($requestData);
        return $response;
    }

    public function downloadBulkLabels($order_ids)
    {
        foreach ($order_ids as $key => $value) {
            $orderIds[] = $value[0];
        }
        $order_ids = implode(",", $orderIds);
        $order_ids = json_encode($order_ids);
        $response = $this->_ausposteParceStandaloneHelper->getBulkLabels($order_ids);
        $response = json_decode($response, true);
        return $response;
    }

    // get shipment rates that includes all surcharges
    public function getShipmentRates($products, $fromPostcode, $toPostcode, $city, $regioncode, $items)
    {
        $sname = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $returnAddressLine1 = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressLine1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? '');
        $returnAddressLine2 = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressLine2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? '');
        $returnAddressLine3 = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressLine3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? '');
        $returnAddressLine4 = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressLine4', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? '');
        
        $coutnry = 'AU';
        $returnAddressPostcode = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressPostcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $returnAddressStateCode = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressStateCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $returnAddressSuburb = trim($this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressSuburb', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        $auspostlabel = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Cresource\Auspostlabel\Collection');
            

        $numProducts = count($products);
        $counter = 0;

        $requestData = [];
        $requestData['shipments'] = [];
        $index = 0;

        $domesticcover = ($this->scopeConfig->getValue('carriers/ausposteParcel/insurance', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1) ? true : false;

        if ($domesticcover) {
            $defaultInsuranceValue = $this->scopeConfig->getValue('carriers/ausposteParcel/defaultInsuranceValue', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        $authority_to_leave = ($this->scopeConfig->getValue('carriers/ausposteParcel/signatureRequired', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1) ? 'false' : 'true';
        $partialDeliveryAllowed = ($this->scopeConfig->getValue('carriers/ausposteParcel/partialDeliveryAllowed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1) ? 'true' : 'false';
        
        foreach ($auspostlabel->getData() as $method) {
            if (strpos($method['type'], "INT'L") !== false || strpos($method['type'], "INTL") !== false || strpos($method['type'], "APGL") !== false) {
                continue;
            }
            $requestData['shipments'][$index]['email_tracking_enabled'] = true;

            // Sender Address
            $requestData['shipments'][$index]['from']['name'] = $sname;
            $requestData['shipments'][$index]['from']['lines'][] = $returnAddressLine1;
            $requestData['shipments'][$index]['from']['lines'][] = $returnAddressLine2;
            $requestData['shipments'][$index]['from']['lines'][] = $returnAddressLine3;
            $requestData['shipments'][$index]['from']['suburb'] = $returnAddressSuburb;
            $requestData['shipments'][$index]['from']['state'] = $returnAddressStateCode;
            $requestData['shipments'][$index]['from']['postcode'] = $returnAddressPostcode;

            // Receiver Address
            $requestData['shipments'][$index]['to']['suburb'] = $city;
            $requestData['shipments'][$index]['to']['state'] = $regioncode;
            $requestData['shipments'][$index]['to']['postcode'] = $toPostcode;
            $requestData['shipments'][$index]['to']['phone'] = "0412345678";
            $requestData['shipments'][$index]['to']['email'] = "jane.smith@smith.com";

            
            if ($domesticcover) {
                $requestData['shipments'][$index]['items'][] = ['product_id' => $method['charge_code'],'length' => $items['length'], 'height' => $items['height'], 'width' => $items['width'], 'weight' => $items['weight'],'authority_to_leave' => $authority_to_leave,'allow_partial_delivery' => $partialDeliveryAllowed , "features" => ["TRANSIT_COVER" => ["attributes"=> ["cover_amount" => $defaultInsuranceValue]]]];
            } else {
                $requestData['shipments'][$index]['items']= ['product_id' => $method['charge_code'], 'length' => $items['length'], 'height' => $items['height'], 'width' => $items['width'], 'weight' => $items['weight'], 'authority_to_leave' => $authority_to_leave, 'allow_partial_delivery' => $partialDeliveryAllowed];
            }
            $index++;
        }
        $response = $this->_ausposteParceStandaloneHelper->getAuspostDomesticRates($requestData);
        return $response;
    }

    //get contract rates
    public function getContractRates($requestData)
    {
        $response = $this->_ausposteParceStandaloneHelper->getAuspostInternationalRates($requestData);
        return $response;
    }

    public function downloadLabel($consignment_number, $eparcel_consignment_id, $label_request_id, $returnLabel = 0)
    {
        $requestData = array();
        $requestData['request_id'] = $label_request_id;
        $requestData['return_label'] = $returnLabel;
        $requestData['eparcel_consignment_id'] = $eparcel_consignment_id;
        $requestData['consignment_number'] = $consignment_number;
        $requestData = json_encode($requestData);
        $result = $this->_ausposteParceStandaloneHelper->downloadLabel($requestData);
        return $result;
    }
}
