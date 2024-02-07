<?php

namespace Machship\Fusedship\Model\Checkout\Block\Cart;

class Shipping extends \Magento\Checkout\Block\Cart\LayoutProcessor
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($merger, $countryCollection, $regionCollection);
    }
    /**
     * Show City in Shipping Estimation
     *
     * @return bool
     * @codeCoverageIgnore
     */
    protected function isCityActive()
    {
        return true;
    }

    public function process($jsLayout)
    {
        $parentJsLayout = parent::process($jsLayout);


        // get fusedship helper data
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');
        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');

        if (!$fusedshipHelper->getIsShowResidentialOption()) {
            return $parentJsLayout;
        }

        $isRes = $checkoutSession->getData('fusedship_is_residential') ?? null;

        if (is_null($isRes)) {
            $isRes = $fusedshipHelper->getDefaultResBusOption();
            $checkoutSession->setData('fusedship_is_residential', filter_var($isRes, FILTER_VALIDATE_BOOLEAN));
        } else {
            $isRes = filter_var($isRes, FILTER_VALIDATE_BOOLEAN) ? '1' : '2';
        }

        $parentJsLayout['components']['block-summary']['children']['block-shipping']['children']['address-fieldsets']['children']['custom-field'] = [
            'component' => 'Magento_Ui/js/form/element/checkbox-set',
            'config' => [
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
            'additionalClasses' => 'is-business-residential',
            'label' => 'The delivery address is a',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [],
            'sortOrder' => "116",
            'multiple' => false,
            'id' => 'custom-field'
        ];


        // FOR address lookup
        $input_customAttributeCode = 'address_lookup_input_field';
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
            'label' => $fusedshipHelper->getLookupFieldTitle(),
            'placeholder' => $fusedshipHelper->getLookupFieldPlaceholder(),
            'provider' => 'checkoutProvider',
            'sortOrder' => 63,
            'validation' => [
                'required-entry' => false
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => $fusedshipHelper->isAddressLookupEnabled(),
        ];


        $parentJsLayout['components']['block-summary']['children']['block-shipping']['children']['address-fieldsets']['children'][$input_customAttributeCode] = $input_customField;


        // Prepend Address Lookup Result dropdown
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
        $parentJsLayout['components']['block-summary']['children']['block-shipping']['children']['address-fieldsets']['children'][$dropdown_customAttributeCode] = $dropdown_customField;

        return $parentJsLayout;
    }
}