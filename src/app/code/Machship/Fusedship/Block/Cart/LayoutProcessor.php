<?php

namespace Machship\Fusedship\Block\Cart;

class LayoutProcessor extends \Magento\Checkout\Block\Cart\LayoutProcessor
{
    /**
     * @var \Magento\Checkout\Block\Checkout\AttributeMerger
     */
    protected $merger;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $regionCollection;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterface
     */
    protected $defaultShippingAddress = null;

    /**
     * @var \Magento\Directory\Model\TopDestinationCountries
     */
    private $topDestinationCountries;

    public function __construct(
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        \Magento\Directory\Model\TopDestinationCountries $topDestinationCountries = null
    ) {
        $this->merger = $merger;
        $this->countryCollection = $countryCollection;
        $this->regionCollection = $regionCollection;
        $this->topDestinationCountries = $topDestinationCountries ?:
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Directory\Model\TopDestinationCountries::class);
    }

    public function process($jsLayout)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');
        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');

        $elements = [
            'city' => [
                'visible' => $this->isCityActive(),
                'formElement' => 'input',
                'label' => __('City'),
                'value' =>  null
            ],
            'country_id' => [
                'visible' => true,
                'formElement' => 'select',
                'label' => __('Country'),
                'options' => [],
                'value' => null
            ],
            'region_id' => [
                'visible' => true,
                'formElement' => 'select',
                'label' => __('State/Province'),
                'options' => [],
                'value' => null
            ],
            'postcode' => [
                'visible' => true,
                'formElement' => 'input',
                'label' => __('Zip/Postal Code'),
                'value' => null
            ],
        ];


        if (!isset($jsLayout['components']['checkoutProvider']['dictionaries'])) {
            $jsLayout['components']['checkoutProvider']['dictionaries'] = [
                'country_id' => $this->countryCollection->loadByStore()->setForegroundCountries(
                    $this->topDestinationCountries->getTopDestinations()
                )->toOptionArray(),
                'region_id' => $this->regionCollection->addAllowedCountriesFilter()->toOptionArray(),
            ];
        }

        if (isset($jsLayout['components']['block-summary']['children']['block-shipping']['children']
            ['address-fieldsets']['children'])
        ) {
            $fieldSetPointer = &$jsLayout['components']['block-summary']['children']['block-shipping']
            ['children']['address-fieldsets']['children'];
            $fieldSetPointer = $this->merger->merge($elements, 'checkoutProvider', 'shippingAddress', $fieldSetPointer);
            $fieldSetPointer['region_id']['config']['skipValidation'] = true;

        }


        if($fusedshipHelper->isAddressLookupEnabled()) {
            $fieldSetPointer = &$jsLayout['components']['block-summary']['children']['block-shipping']
            ['children']['address-fieldsets']['children'];

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
                'dataScope' => 'shippingAddress.custom_attributes' . '.' . $dropdown_customAttributeCode,
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
                'formElement' => 'select'
            ];

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
                'dataScope' => 'shippingAddress.custom_attributes' . '.' . $input_customAttributeCode,
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
                'visible' => true,
                'formElement' => 'input'
            ];


            $fusedship_destination = $checkoutSession->getFusedshipDestination();

            if(is_array($fusedship_destination) && !empty($fusedship_destination)) {
                $input_customField_value = '';

                if(isset($fusedship_destination['city'])) {
                    $input_customField_value .= $fusedship_destination['city'];
                }

                if(isset($fusedship_destination['postal_code'])) {
                    $input_customField_value .= ', ' . $fusedship_destination['postal_code'];
                }

                if(isset($fusedship_destination['province'])) {
                    $input_customField_value .= ', ' . $fusedship_destination['province'];
                }

                if(!empty($input_customField_value)) {
                    $input_customField['value'] = $input_customField_value;
                }
            }


            $fieldSetPointer[$input_customAttributeCode] = $input_customField;
            $fieldSetPointer[$dropdown_customAttributeCode] = $dropdown_customField;

            $fieldSetPointer['postcode']['visible'] = false;
            $fieldSetPointer['region_id']['visible'] = false;
            $fieldSetPointer['country_id']['visible'] = false;
            $fieldSetPointer['city']['visible'] = false;
        }



        return $jsLayout;
    }
}
