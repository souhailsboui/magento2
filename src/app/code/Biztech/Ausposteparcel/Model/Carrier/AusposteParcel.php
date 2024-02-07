<?php

namespace Biztech\Ausposteparcel\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class AusposteParcel extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements CarrierInterface
{
    const XML_PATH_AUSPOST_SIGNATURE_REQUIRED = 'carriers/ausposteParcel/signature_required';

    public $_code = 'ausposteParcel';
    public $_default_condition_name = 'package_weight';
    public $_conditionNames = [];
    public $scopeConfig;
    public $ausposteParcelApi;
    public $ausposteParcelInfoHelper;
    public $ausposteParcelCarrierPackFactory;
    public $_rateResultFactory;
    public $_rateMethodFactory;
    public $_rateErrorFactory;
    public $_cart;
    public $_checkoutsession;
    public $_adminCheckoutSession;
    public $_state;
    public $auspostlabel;
    protected $freeshipping;
    protected $helper;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Biztech\Ausposteparcel\Model\Api $ausposteParcelApi,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper,
        \Biztech\Ausposteparcel\Model\Carrier\PackFactory $ausposteParcelCarrierPackFactory,
        ObjectManagerInterface $objectmanager,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magento\Backend\Model\Session\Quote $adminCheckoutSession,
        \Biztech\Ausposteparcel\Model\Cresource\Auspostlabel\Collection $auspostlabel,
        \Psr\Log\LoggerInterface $logger,
        \Biztech\Ausposteparcel\Helper\Data $helper,
        \Magento\Framework\App\State $state,
        array $data = []
    ) {
        $this->ausposteParcelCarrierPackFactory = $ausposteParcelCarrierPackFactory;
        $this->scopeConfig = $scopeConfig;
        $this->ausposteParcelApi = $ausposteParcelApi;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->_objectManager = $objectmanager;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->_cart = $cart;
        $this->_checkoutsession = $checkoutsession;
        $this->_adminCheckoutSession = $adminCheckoutSession;
        $this->_state = $state;
        $this->helper = $helper;
        $this->auspostlabel = $auspostlabel;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        foreach ($this->getCode('condition_name') as $k => $v) {
            $this->_conditionNames[] = $k;
        }
    }

    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {  
        if ((!$this->scopeConfig->getValue('carriers/ausposteParcel/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) || (!in_array($request->getStoreId(), $this->helper->getAllWebsites()))) {
            return false;
        }
        $result = $this->_rateResultFactory->create();
        $country = $request->getDestCountryId();
        $products = [];

        if (!$request->getDestPostcode()) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('ausposteParcel');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage('Please enter delivery address postal code to view available shipping methods');
            $result->append($error);
            return $result;
        }

        if ($this->scopeConfig->getValue('carriers/ausposteParcel/hiderates', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            if ($this->_state->getAreaCode() != 'adminhtml') {
                return false;
            }
        }


        $freeItem = $this->getFreeItemsCount($request);
        $signatureRequired = $this->scopeConfig->getValue(self::XML_PATH_AUSPOST_SIGNATURE_REQUIRED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $total_weight = 0;
        $quote = $this->_checkoutsession->getQuote();
        $length_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/length_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $width_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/width_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $height_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/height_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                
                /* Do not allow configurable product to as the dimensions can be differ for its simple products */
                if ($item->getHasChildren() && $item->getProduct()->getTypeId() == "configurable") {
                    continue;
                }
                $productObj = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($item->getProductId());

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    if ($item->getParentItem()->getHasChildren()) {
                        if ($item->getParentItem()->getFreeShipping()) {
                            continue;
                        }
                    }
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            continue 2;
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    continue;
                }
                if ($item->getProduct()->getTypeId() == "bundle" && $item->isShipSeparately()) {
                    continue;
                }

                /* Do not allow simple products of bundle product */
                if ($item->getParentItem()) {
                    if ($item->getParentItem()->getProduct()->getTypeId() == "bundle" && !$item->isShipSeparately()) {
                        continue;
                    }
                }
                $itemsweight = (is_null($item->getWeight())) ? number_format($this->scopeConfig->getValue('carriers/ausposteParcel/default_weight', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), 2, '.', '') : number_format($item->getWeight(), 2, '.', '');
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    $productItemObj = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($item->getProductId());
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $product_id = $child->getProductId();
                            $productObj = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
                            $item_qty = $item->getParentItem() ? $item->getParentItem()->getQty() : $item->getQty();

                            for ($i = 1; $i <= $item_qty; $i++) {
                                $boxes[$item->getId() . "-" . $product_id] = [
                                    'length' => $productObj->getData($length_attr) ? $productObj->getData($length_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/length_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                                    'width' => $productObj->getData($width_attr) ? $productObj->getData($width_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/width_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                                    'height' => $productObj->getData($height_attr) ? $productObj->getData($height_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/height_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                                ];
                                $products[] = [
                                    'sku' => $productObj->getSku(),
                                    'length' => $productObj->getData($length_attr) ? $productObj->getData($length_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/length_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                                    'width' => $productObj->getData($width_attr) ? $productObj->getData($width_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/width_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                                    'height' => $productObj->getData($height_attr) ? $productObj->getData($height_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/height_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                                    'price' => $productObj->getFinalPrice(),
                                    'weight' => $itemsweight
                                ];
                                $total_weight += $itemsweight;
                            }
                        }
                    }
                } else {
                    $product_id = $item->getProductId();
                    $productObj = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
                    if ($item->getParentItem()) {
                        if ($item->getParentItem()->getProduct()->getTypeId() == "bundle" && $item->isShipSeparately()) {
                            $item_qty = $item->getQty();
                            $parent_qty = $item->getParentItem() ? $item->getParentItem()->getQty() : 1;
                        } else {
                            $item_qty = $item->getParentItem() ? $item->getParentItem()->getQty() : $item->getQty();
                        }
                    } else {
                        $item_qty = $item->getParentItem() ? $item->getParentItem()->getQty() : $item->getQty();
                    }
                    if ($item->getParentItem()) {
                        if ($item->getParentItem()->getProduct()->getTypeId() == "bundle" && $item->isShipSeparately()) {
                            $itemWeight = $item_qty * ($itemsweight);
                            $parentWeight = $itemWeight * $parent_qty;
                            $total_weight += $parentWeight;
                        } else {
                            $total_weight += $item_qty * ($item->getParentItem() ? $item->getParentItem()->getWeight() : $itemsweight);
                        }
                    } else {
                        $total_weight += $item_qty * ($item->getParentItem() ? $item->getParentItem()->getWeight() : $itemsweight);
                    }

                    $prd_length = $productObj->getData($length_attr);
                    $prd_width = $productObj->getData($width_attr);
                    $prd_height = $productObj->getData($height_attr);
                    if ($this->scopeConfig->getValue('carriers/ausposteParcel/auspost_allow_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                        $prd_length = $productObj->getData($length_attr) ? $productObj->getData($length_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/length_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $prd_width = $productObj->getData($width_attr) ? $productObj->getData($width_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/width_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $prd_height = $productObj->getData($height_attr) ? $productObj->getData($height_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/height_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    }

                    for ($i = 1; $i <= $item_qty; $i++) {
                        $boxes[$item->getId()] = [
                            'length' => $prd_length,
                            'width' => $prd_width,
                            'height' => $prd_height
                        ];
                        $products[] = [
                            'sku' => $productObj->getSku(),
                            'length' => $productObj->getData($length_attr) ? $productObj->getData($length_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/length_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                            'width' => $productObj->getData($width_attr) ? $productObj->getData($width_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/width_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                            'height' => $productObj->getData($height_attr) ? $productObj->getData($height_attr) : $this->scopeConfig->getValue('carriers/ausposteParcel/height_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                            'price' => $productObj->getFinalPrice(),
                            'weight' => $itemsweight
                        ];
                        $boxes1[] = [
                            'length' => $prd_length,
                            'width' => $prd_width,
                            'height' => $prd_height
                        ];
                    }
                }
            }

            if (isset($boxes) === false && isset($boxes1) === false && $request->getPackageQty() == $freeItem) {
                $errormsg = $this->getConfigData('specificerrmsg');
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier('ausposteParcel');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($errormsg);
                $result->append($error);
                return $result;
            }

            $getQuote = $this->_cart->getQuote()->getData();
            $totalQuantity = "";
            if (isset($getQuote)) {
                if (isset($getQuote['items_qty'])) {
                    $totalQuantity = $getQuote['items_qty'];
                }
            }

            $freeshippingenable = $this->getConfigData('enablefreeshipping');
            $this->freeshipping = false;
            if ($freeshippingenable) {
                $subTotal = $request->getBaseSubtotalInclTax();
                $requiresubtotal = $this->getConfigData('freeshippingsubtotal');
                if ($subTotal >= $requiresubtotal) {
                    $this->freeshipping = true;
                } else {
                    $this->freeshipping = false;
                }
            }

            $adminhtmltotalQuantity = "";
            if ($this->_state->getAreaCode() == 'adminhtml') {
                if (isset($this->_adminCheckoutSession)) {
                    $adminhtmlQuote = $this->_adminCheckoutSession->getQuote()->getData();
                    if (isset($adminhtmlQuote['items_qty'])) {
                        $adminhtmltotalQuantity = $adminhtmlQuote['items_qty'];
                    }
                }
            }
            if ($totalQuantity == 1 || $adminhtmltotalQuantity == 1) {

                $length_value = 0;
                $width_value = 0;
                $height_value = 0;

                if($this->scopeConfig->getValue('carriers/ausposteParcel/auspost_allow_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
                    $length_value = $this->scopeConfig->getValue('carriers/ausposteParcel/length_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $width_value = $this->scopeConfig->getValue('carriers/ausposteParcel/width_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $height_value = $this->scopeConfig->getValue('carriers/ausposteParcel/height_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                }

                $length = $prd_length = $productObj->getData($length_attr) ? $productObj->getData($length_attr) : $length_value;
                $width = $prd_width = $productObj->getData($width_attr) ? $productObj->getData($width_attr) : $width_value;
                $height = $productObj->getData($height_attr) ? $productObj->getData($height_attr) : $height_value;
            } else {
                $lp = $this->ausposteParcelCarrierPackFactory->create();
                $lp->pack($boxes1);
                $c_size = $lp->get_container_dimensions();
                $length = $c_size['length'];
                $width = $c_size['width'];
                $height = $c_size['height'];
            }
            if ($this->scopeConfig->getValue('carriers/ausposteParcel/weight_unit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == "gm") {
                $total_weight = $total_weight / 1000;
            }
        }

        $toPostcode = $request['dest_postcode'];
        $fromPostcode = $this->scopeConfig->getValue('carriers/ausposteParcel/returnAddressPostcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $length = (round($length ?? 0) < 5 && $this->scopeConfig->getValue('carriers/ausposteParcel/auspost_allow_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) ? 5 : $length;
        $width = (round($width ?? 0) < 5 && $this->scopeConfig->getValue('carriers/ausposteParcel/auspost_allow_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) ? 5 : $width;
        $height = (round($height ?? 0) < 5 && $this->scopeConfig->getValue('carriers/ausposteParcel/auspost_allow_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) ? 5 : $height;
        
        $flag = 0;
        $weightTotal = (float) $total_weight;
        $wT = number_format($weightTotal, 2); // 3.12
        $city = $request->getDestCity();
        $toPostcode = $request['dest_postcode'];
        $regioncode = $request->getDestRegionCode();

        $configumethod = $this->getConfigData('contractproducttype');
        $enabledmethod = explode(",", $configumethod ?? '');

        $adminarea = false;

        if ('adminhtml' === $this->_state->getAreaCode()) {
            $adminarea = true;
        }

        if ($country == 'AU') {
            $smethods = [];
            $shippingmethod = [];


            if (sizeof($this->auspostlabel) > 0) {
                foreach ($this->auspostlabel as $item) {
                    $shippingmethod[] = ['value' => $item->getChargeCode(), 'label' => $item->getType()];
                    $smethods[$item->getChargeCode()] = $item->getType();
                }
            }
            $items = ['length' => round($length ?? 0), 'width'  => round($width ?? 0), 'height'  => round($height ?? 0), 'weight'  => $wT];
            $response = $this->ausposteParcelApi->getShipmentRates($products, $fromPostcode, trim($toPostcode ?? ''), trim($city ?? ''), $regioncode, $items);
            
            if ($response == null) {
                return false;
            }
            $responseData = json_decode($response);
            
            if (isset($responseData->errors)) {

                if(isset($responseData->errors[0]->message)){
                    $errors = $responseData->errors[0];
                    $errormsg = ($adminarea) ? "Error Code : " . $errors->code . " Message: " . $errors->message : $this->getConfigData('specificerrmsg');
                }else{
                    $errormsg = $this->getConfigData('specificerrmsg');
                }
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier('ausposteParcel');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($errormsg);
                $result->append($error);
                return $result;
            } else {
                $handlingFee = (!is_null($this->getConfigData('handling_fee'))) ? $this->getConfigData('handling_fee') : 0;
                $handlingType = $this->getConfigData('handling_type');
                $freeshipmethod = $this->getConfigData('freeshippingproducttype');
                $selectedfreeshipping = explode(",", $freeshipmethod!=null?$freeshipmethod:'');

                $flag = 1;
                if (!isset($responseData->shipments)) {
                    return false;
                }
                foreach ($responseData->shipments as $methods) {
                    if (in_array($methods->items[0]->product_id, $enabledmethod)) {
                        $methodname = $smethods[$methods->items[0]->product_id];
                        $quoteRate = $methods->shipment_summary->total_cost;
                        if ($handlingType == "F") {
                            $quoteRate += $handlingFee;
                            $quoteRate = round($quoteRate, 2);
                        } elseif ($handlingType == "P") {
                            $quoteRate += (($quoteRate * $handlingFee) / 100);
                            $quoteRate = round($quoteRate, 2);
                        }
                        $method = $this->_rateMethodFactory->create();
                        $method->setCarrier('ausposteParcel');
                        $method->setCarrierTitle($this->getConfigData('title'));
                        $method->setMethod('ausposteParcel-' . $methods->items[0]->product_id);

                        $method->setMethodTitle($methodname);

                        if ($this->freeshipping) {
                            if (in_array($methods->items[0]->product_id, $selectedfreeshipping)) {
                                $method->setPrice(0);
                                $method->setCost(0);
                            } else {
                                $method->setPrice($quoteRate);
                                $method->setCost($quoteRate);
                            }
                        } else {
                            $method->setPrice($quoteRate);
                            $method->setCost($quoteRate);
                        }
                        $result->append($method);
                    }
                }
                return $result;
            }
        } else {
            $requestData = [
                'from' => ['postcode' => $fromPostcode],
                'to' => ['postcode' => $toPostcode, "country" => $country],
                'items' => ['length' => round($length), 'width' => round($width), 'height' => round($height), 'weight' => $wT],
                'prices' => ['options' => ['signature_on_delivery_option' => false]]
            ];
            $response = $this->ausposteParcelApi->getContractRates($requestData);
        }
        $responseData = json_decode($response);

        if (isset($responseData->errors)) {
            if(isset($responseData->errors[0]->message)){	
                $err = ($adminarea) ? " Message: " .$responseData->errors[0]->message : $this->getConfigData('specificerrmsg');	
            }else{	
                $err = $this->getConfigData('specificerrmsg');;	
            }
            $err = ($adminarea) ? " Message: " .$responseData->errors : $this->getConfigData('specificerrmsg');
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('ausposteParcel');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($err);
            $result->append($error);
            return $result;
        } else {
            if (!isset($responseData->items)) {
                    return false;
            }
            foreach ($responseData->items as $prices) {
                if (!empty($prices->prices)) {
                    $flag = 1;
                    $handlingFee = (!is_null($this->getConfigData('handling_fee'))) ? $this->getConfigData('handling_fee') : 0;
                    $handlingType = $this->getConfigData('handling_type');
                    $freeshipmethod = $this->getConfigData('freeshippingproducttype');
                    $selectedfreeshipping = explode(",", $freeshipmethod!=null?$freeshipmethod:'');
                    foreach ($prices->prices as $rates) {
                        if (in_array($rates->product_id, $enabledmethod)) {
                            $quoteRate = $rates->calculated_price;

                            $quoteRate = $rates->calculated_price;
                            if ($handlingType == "F") {
                                $quoteRate += $handlingFee;
                                $quoteRate = round($quoteRate, 2);
                            } elseif ($handlingType == "P") {
                                $quoteRate += (($quoteRate * $handlingFee) / 100);
                                $quoteRate = round($quoteRate, 2);
                            }
                            $method = $this->_rateMethodFactory->create();
                            $method->setCarrier('ausposteParcel');
                            $method->setMethod('ausposteParcel-' . $rates->product_id);
                            $method->setCarrierTitle($this->scopeConfig->getValue('carriers/ausposteParcel/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                            $method->setMethodTitle($rates->product_type);

                            if ($this->freeshipping) {
                                if (in_array($rates->product_id, $selectedfreeshipping)) {
                                    $method->setPrice(0);
                                    $method->setCost(0);
                                } else {
                                    $method->setPrice($quoteRate);
                                    $method->setCost($quoteRate);
                                }
                            } else {
                                $method->setPrice($quoteRate);
                                $method->setCost($quoteRate);
                            }
                            $result->append($method);
                        }
                    }
                    return $result;
                } elseif (!empty($prices->errors)) {
                    $flag = 0;
                } elseif (empty($prices->prices)) {

                    if (isset($responseData->warnings) && isset($responseData->warnings[0]->message) && isset($responseData->warnings[0]->code)) {
                        $err = ($adminarea) ? "Error Code : " . $responseData->warnings[0]->code . " Message: " . $responseData->warnings[0]->message : $this->getConfigData('specificerrmsg');
                    }else{
                        $err = $this->getConfigData('specificerrmsg');
                    }
                    $error = $this->_rateErrorFactory->create();
                    $error->setCarrier('ausposteParcel');
                    $error->setCarrierTitle($this->getConfigData('title'));
                    $error->setErrorMessage($err);
                    $result->append($error);
                    return $result;
                }
            }
        }
        return $result;
    }

    /**
     * @param  RateRequest $request
     * @return int
     */
    private function getFreeItemsCount($request)
    {
        $freeItems = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    $freeItems += $this->getFreeItemsCountFromChildren($item);
                } elseif ($item->getFreeShipping()) {
                    $freeItems += $item->getQty();
                }
            }
        }
        return $freeItems;
    }

    /**
     * @param  mixed $item
     * @return mixed
     */
    private function getFreeItemsCountFromChildren($item)
    {
        $freeItems = 0;
        foreach ($item->getChildren() as $child) {
            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                $freeItems += $item->getQty() * $child->getQty();
            }
        }
        return $freeItems;
    }


    public function geteParcelRates($request)
    {
        if (!$request->getConditionName()) {
            $request->setConditionName($this->scopeConfig->getValue('carriers/ausposteParcel/conditionName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('carriers/ausposteParcel/conditionName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : $this->_default_condition_name);
        }

        if (!$request->getDestPostcode()) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('ausposteParcel');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage('Please enter delivery address postal code to view available shipping methods');
            $result->append($error);
            return $result;
        }


        $result = $this->_rateResultFactory->create();

        $rates = $this->getRate($request);

        if (is_array($rates)) {
            if (sizeof($rates) > 0) {
                foreach ($rates as $rate) {
                    if (!empty($rate) && $rate['price'] >= 0) {
                        $method = $this->_rateMethodFactory->create();

                        $method->setCarrier('ausposteParcel');
                        $method->setCarrierTitle($this->getConfigData('title'));

                        $method->setMethod($this->getChargeCode($rate));

                        $method->setMethodChargeCode($rate['charge_code']);

                        $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);

                        $freeshippingResult = $this->ausposteParcelInfoHelper->getFreeshipping($rate['charge_code'], $request->getBaseSubtotalInclTax());
                        if ($freeshippingResult) {
                            if ($freeshippingResult['to_amount'] > 0) {
                                if ($request->getBaseSubtotalInclTax() <= $freeshippingResult['to_amount']) {
                                    $freeshippingForMinimumAmount = $freeshippingResult['minimum_amount'];
                                }
                            } else {
                                $freeshippingForMinimumAmount = $freeshippingResult['minimum_amount'];
                            }

                            if ($freeshippingForMinimumAmount == 0) {
                                $shippingPrice = 0;
                                $method->setMethodTitle(__('Free Shipping'));
                            } else {
                                $shippingPrice = $freeshippingForMinimumAmount;
                                $method->setMethodTitle($rate['delivery_type']);
                            }
                        } else {
                            $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
                            $method->setMethodTitle($rate['delivery_type']);
                        }

                        $method->setPrice($shippingPrice);
                        $method->setCost($rate['cost']);
                        $method->setDeliveryType($rate['delivery_type']);

                        $result->append($method);
                    }
                }
            } else {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
                $result->append($error);
                return $result;
            }
        } else {
            if (!empty($rates) && $rates['price'] >= 0) {
                $method = $this->_rateMethodFactory->create();

                $method->setCarrier('ausposteParcel');
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod('bestway');
                $method->setMethodTitle($this->getConfigData('name'));

                $method->setMethodChargeCode($rates['charge_code']);

                $shippingPrice = $this->getFinalPriceWithHandlingFee($rates['price']);

                $freeshippingForMinimumAmount = $this->ausposteParcelInfoHelper->getFreeshipping($rate['charge_code']);
                if ($freeshippingForMinimumAmount && ($request->getBaseSubtotalInclTax() >= $freeshippingForMinimumAmount)) {
                    $shippingPrice = 0;
                    $method->setMethodTitle(__('Free Shipping'));
                } else {
                    $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
                    $method->setMethodTitle($rate['delivery_type']);
                }

                $method->setPrice($shippingPrice);
                $method->setCost($rates['cost']);
                $method->setDeliveryType($rates['delivery_type']);

                $result->append($method);
            } else {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
                $result->append($error);
                return $result;
            }
        }
        return $result;
    }

    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        return $this->_objectManager->create('Biztech\Ausposteparcel\Model\Carrier\AusposteParcel')->getRate($request);
    }

    protected function getChargeCode($rate)
    {
        return $rate['charge_code'];
    }

    public function getCode($type, $code = '')
    {
        $codes = [
            'condition_name' => [
                'package_weight' => __('Weight vs. Destination'),
                'package_value' => __('Price vs. Destination'),
                'package_qty' => __('# of Items vs. Destination'),
            ],
            'condition_name_short' => [
                'package_weight' => __('Weight (and above)'),
                'package_value' => __('Order Subtotal (and above)'),
                'package_qty' => __('# of Items (and above)'),
            ],
        ];

        if (!isset($codes[$type])) {
            throw new LocalizedException(new Phrase('Invalid Table Rate code type: %1', $type));
        }

        if ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw new LocalizedException(new Phrase('Invalid Table Rate code for type %1: %1', $type, $code));
        }

        return $codes[$type][$code];
    }

    public function getAllowedMethods()
    {
        return ['ausposteParcel' => $this->getConfigData('name')];
    }

    public function getTrackingInfo($number)
    {
        $custom = [];
        $custom['title'] = $this->getConfigData('title');
        $custom['number'] = '<a href="http://auspost.com.au/track/track.html?id=' . $number . '" target="_blank">' . $number . '</a>';
        return $custom;
    }
}
