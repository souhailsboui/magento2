<?php

namespace Biztech\Ausposteparcel\Plugin\Checkout;

class LayoutProcessorPlugin
{
    protected $storeManager;
    protected $scopeConfig;
    protected $helper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Biztech\Ausposteparcel\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $IsValidationEnable = $this->scopeConfig->getValue('carriers/ausposteParcel/enable_addressvalidationfront', $storeScope);

        if (($IsValidationEnable == 1) && (in_array($this->storeManager->getStore()->getStoreId(), $this->helper->getAllWebsites()))) {
            $url = $this->storeManager->getStore()->getBaseUrl() . 'ausposteparcel/validate/address';

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['shipping-address-fieldset']['children']['validate_auspost_address'] = [
                'component' => 'Biztech_Ausposteparcel/js/checkout/action/button',
                'additionalClasses' => 'auspost_validateaddresss',
                'elementTmpl' => 'Biztech_Ausposteparcel/form/element/button',
                'url' => $url,
                'dataScope' => 'shippingAddress.validate_auspost_address',
                'label' => 'Validate Ausposteparcel',
                'title' => 'Validate Address',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'sortOrder' => 999,
                'id' => 'validate_auspost_address'
                    ];
        }

        return $jsLayout;
    }
}
