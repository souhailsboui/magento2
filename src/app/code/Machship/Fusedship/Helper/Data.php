<?php

namespace Machship\Fusedship\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    // protected $api_url = 'https://sync-beta.fusedship.com/magento2/';
    protected $api_url = 'https://sync.fusedship.com/magento2/';

	const XML_PATH_FUSEDSHIP = 'fusedship_config_section/';

	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

    public function getWeightUnit() {
        return $this->scopeConfig->getValue(
            'general/locale/weight_unit',
            ScopeInterface::SCOPE_STORE
        );
    }

	public function getGeneralConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_FUSEDSHIP .'general_config_group/'. $code, $storeId);
	}

    public function getQuotingOptionsConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_FUSEDSHIP .'quoting_options_group/'. $code, $storeId);
    }

    public function getShippingOptionsConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_FUSEDSHIP .'shipping_options_group/'. $code, $storeId);
    }


    public function getProductWidgetOptionsConfig($code, $storeId = null) {
        return $this->getConfigValue(self::XML_PATH_FUSEDSHIP .'product_widget_options_group/'. $code, $storeId);
    }

     /**
     * Product Widget Options Configuration
     */

    public function isProductWidgetEnabled() {
        return $this->getProductWidgetOptionsConfig('enable_product_widget') && $this->fusedshipEnablePlugin();
    }


    public function displayProductWidgetInPopup() {
        return $this->getProductWidgetOptionsConfig('display_in_popup');
    }

    public function productWidgetTitle() {
        return $this->getProductWidgetOptionsConfig('product_widget_title');
    }

    public function productWidgetDescription() {
        return $this->getProductWidgetOptionsConfig('product_widget_description');
    }

    public function productWidgetLookupFieldTitle() {
        return $this->getProductWidgetOptionsConfig('product_widget_lookup_field_title');
    }

    public function productWidgetLookupFieldPlaceholder() {
        return $this->getProductWidgetOptionsConfig('product_widget_lookup_field_placeholder');
    }

    public function popupButtonLabel() {
        return $this->getProductWidgetOptionsConfig('popup_button_label');
    }


    /**
     * Quoting Options Configuration
     */

    public function isLiveRatesEnabled() {
        return $this->getQuotingOptionsConfig('enable_live_rates') && $this->fusedshipEnablePlugin();
    }

    public function isAddressLookupEnabled() {
        return $this->getQuotingOptionsConfig('enable_address_lookup') && $this->fusedshipEnablePlugin();
    }

    public function isPostcodeLocked() {
        return $this->getQuotingOptionsConfig('lock_postcode') && $this->fusedshipEnablePlugin();
    }

    public function isSuburbLocked() {
        return $this->getQuotingOptionsConfig('lock_suburb') && $this->fusedshipEnablePlugin();
    }

    public function getLookupFieldTitle() {
        return $this->getQuotingOptionsConfig('lookup_field_title');
    }

    public function getLookupFieldPlaceholder() {
        return $this->getQuotingOptionsConfig('lookup_field_placeholder');
    }

    public function rateMessage() {
        $rateMessage = $this->getQuotingOptionsConfig('rate_message');
        return empty($rateMessage) ? 'Getting Rates From Carriers' : $rateMessage;
    }

    public function rateTriggerCharacter() {
        $rateTriggerCharacter = $this->getQuotingOptionsConfig('rate_trigger_character');
        return empty($rateTriggerCharacter) ? 4 : $rateTriggerCharacter;
    }

    public function isMoveCountryAboveAddress() {
        $rateTriggerCharacter = $this->getQuotingOptionsConfig('move_country_above_address');
        return empty($rateTriggerCharacter) ? false : true;
    }

    public function getIsShowResidentialOption() {
        return $this->getQuotingOptionsConfig('enable_is_show_residential_option');
    }

    public function getDefaultResBusOption() {
        return $this->getQuotingOptionsConfig('default_residential_business_option') ?? '1';
    }

    /**
     * Shipping Options Configuration
     *
     */

    public function isShippingMarginEnabled() {
        return $this->getShippingOptionsConfig('enable_shipping_margin');
    }

    public function getShippingMarginType() {
        return $this->getShippingOptionsConfig('shipping_margin_type');
    }

    public function getShippingMarginValue() {
        return $this->getShippingOptionsConfig('shipping_margin_value');
    }

    public function getShippingRoundingOption() {
        return $this->getShippingOptionsConfig('round_shipping_price');
    }


    /**
     * General Configuration
     *
     */
    public function getFusedshipIntegrationKey() {
        return $this->getGeneralConfig('fusedship_integration_key');
    }


    public function fusedshipEnablePlugin() {
        return $this->getGeneralConfig('enable_plugin');
    }

    public function isDebugEnabled() {
        return $this->getGeneralConfig('enable_debug');
    }

    public function getApiUrl() {
        return $this->api_url;
    }
}
