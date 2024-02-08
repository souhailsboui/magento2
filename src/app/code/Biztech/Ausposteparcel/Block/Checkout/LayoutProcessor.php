<?php

namespace Biztech\Ausposteparcel\Block\Checkout;

/**
 * Checkout cart shipping estimation block plugin
 */
class LayoutProcessor extends \Magento\Checkout\Block\Cart\LayoutProcessor
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    private $topDestinationCountries;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        \Magento\Directory\Model\TopDestinationCountries $topDestinationCountries = null
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($merger, $countryCollection, $regionCollection);
        $this->topDestinationCountries = $topDestinationCountries ?:
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Directory\Model\TopDestinationCountries::class);
    }


    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function process($jsLayout)
    {
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
            ]
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

        $jsLayout['components']['block-summary']['children']['block-shipping']['children']['address-fieldsets']['children']['city']['sortOrder'] = 105;
        $jsLayout['components']['block-summary']['children']['block-shipping']['children']['address-fieldsets']['children']['postcode']['sortOrder'] = 108;
        return $jsLayout;
    }


    /**
     * Show City in Shipping Estimation
     *
     * @return             bool
     * @codeCoverageIgnore
     */
    protected function isCityActive()
    {
        return true;
    }
}
