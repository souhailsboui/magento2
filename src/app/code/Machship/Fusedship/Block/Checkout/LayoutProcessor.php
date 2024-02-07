<?php

namespace Machship\Fusedship\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{

    private $objectManager;
    private $checkoutSession;
    private $fusedshipHelper;

    public function __construct() {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->fusedshipHelper = $this->objectManager->get('Machship\Fusedship\Helper\Data');
        $this->checkoutSession = $this->objectManager->get('\Magento\Checkout\Model\Session');
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout) {

        $dropdown_customAttributeCode = 'address_lookup_results_field';
        $dropdown_customField = [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                // customScope is used to group elements within a single form (e.g. they can be validated separately)
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
            ],
            'additionalClasses' => 'address-lookup-results-field',
            'label' => null,
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'validation' => [
                'required-entry' => false
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => false,
        ];

        $input_customAttributeCode = 'address_lookup_input_field';
        $input_customAttributeCode_disabled = $this->fusedshipHelper->isAddressLookupEnabled() ? '' : '.disabled';
        $input_customField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                // customScope is used to group elements within a single form (e.g. they can be validated separately)
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
            ],
            'additionalClasses' => 'address-lookup-input-field',
            'label' => $this->fusedshipHelper->getLookupFieldTitle(),
            'placeholder' => $this->fusedshipHelper->getLookupFieldPlaceholder(),
            'provider' => 'checkoutProvider',
            'sortOrder' => 63,
            'validation' => [
                'required-entry' => false
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => false,
        ];


        $fusedship_destination = $this->checkoutSession->getFusedshipDestination();
        $fdRegionId = "";
        $fdCity = "";
        $fdPostcode = "";
        $fdCountry = "";

        if(is_array($fusedship_destination) && !empty($fusedship_destination)) {
            // fill up locations fields city, postalcode, region

            if(isset($fusedship_destination['city'])) {
                $fdCity = $fusedship_destination['city'];
            }

            if(isset($fusedship_destination['postal_code'])) {
                $fdPostcode = $fusedship_destination['postal_code'];
            }


            if (empty($fusedship_destination['region_id'])) {
                // this happens if we used fusedship region address lookup, region_id is not available
                $region = $this->objectManager->create('Magento\Directory\Model\Region');
                $region = $region->loadByCode($fusedship_destination['province'], 'AU');
                $fdRegionId = $region->getId();
            } else {
                $fdRegionId = $fusedship_destination['region_id'];
            }


        }

        // // Prepend Address Lookup Input Field
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$input_customAttributeCode] = $input_customField;

        // Prepend Address Lookup Result dropdown
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$dropdown_customAttributeCode] = $dropdown_customField;



        if ($this->fusedshipHelper->getIsShowResidentialOption()) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['custom_field'] = $this->createIsResidential();
        }

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['is_residential']['visible'] = false;



        if($this->fusedshipHelper->isAddressLookupEnabled()) {

            // Select Country first

            if ($this->fusedshipHelper->isMoveCountryAboveAddress()) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id']['sortOrder'] = 45;
            }


            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$input_customAttributeCode]['sortOrder'] = 80;

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$input_customAttributeCode]['visible'] = true;

            // Update Postcode disable/enable
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['disabled'] = $this->fusedshipHelper->isPostcodeLocked();

            // Update suburb disable/enable
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['disabled'] = $this->fusedshipHelper->isSuburbLocked();


        } else {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$input_customAttributeCode]['visible'] = false;
        }


        // make sure that its not empty so it wont overwrite the default
        if (!empty($fdCountry)) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id']['value'] = $fdCountry;
        }

        if (!empty($fdRegionId)) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['config']['default'] = $fdRegionId;
        }

        if (!empty($fdCity)) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['city']['value'] = $fdCity;
        }

        if (!empty($fdPostcode)) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['value'] = $fdPostcode;
        }

        return $jsLayout;
    }

    private function createIsResidential() {

        $isRes = $this->checkoutSession->getData('fusedship_is_residential') ?? null;

        if (is_null($isRes)) {
            $isRes = $this->fusedshipHelper->getDefaultResBusOption();
            $this->checkoutSession->setData('fusedship_is_residential', filter_var($isRes, FILTER_VALIDATE_BOOLEAN));
        } else {
            $isRes = filter_var($isRes, FILTER_VALIDATE_BOOLEAN) ? '1' : '2';
        }

        // Add custom field to the shipping address form
        return [
            'component' => 'Magento_Ui/js/form/element/checkbox-set',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/checkbox-set',
            ],
            'options' => [
                [
                    'value' => '1',
                    'label' => 'Residential'
                ],
                [
                    'value' => '2',
                    'label' => 'Business'
                ]
            ],
            'value' => $isRes,
            'validation' => [
                'required-entry' => true
            ],
            'additionalClasses' => 'is-business-residential',
            'label' => 'The delivery address is a',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'sortOrder' => 200,
            'multiple' => false,
            'id' => 'custom-field'
        ];
    }
}