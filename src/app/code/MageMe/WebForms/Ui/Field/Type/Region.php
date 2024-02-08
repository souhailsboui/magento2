<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Ui\Field\Type;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Ui\FieldResultFormInterface;
use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Model\Field\Type\Country;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\BodyTmpl;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Registry;
use Magento\Ui\Component\Form;

class Region extends AbstractField implements FieldResultListingColumnInterface, FieldResultFormInterface
{
    const COUNTRY_FIELD_ID = Type\Region::COUNTRY_FIELD_ID;
    const COUNTRY_CODE = Type\Region::COUNTRY_CODE;

    /**
     * @var RegionCollectionFactory
     */
    protected $regCollectionFactory;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @param CollectionFactory $countryCollectionFactory
     * @param Registry $registry
     * @param RegionCollectionFactory $regCollectionFactory
     */
    public function __construct(
        CollectionFactory       $countryCollectionFactory,
        Registry                $registry,
        RegionCollectionFactory $regCollectionFactory
    ) {
        $this->registry                 = $registry;
        $this->regCollectionFactory     = $regCollectionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::COUNTRY_FIELD_ID => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::COUNTRY_FIELD_ID,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Country Field'),
                                    'additionalInfo' => __('Connect to country field to populate the list with region options.'),
                                    'options' => $this->getCountryFields(),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::COUNTRY_CODE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::COUNTRY_CODE,
                                    'visible' => 0,
                                    'sortOrder' => 66,
                                    'label' => __('Country'),
                                    'additionalInfo' => __('Link directly to the country.'),
                                    'options' => $this->getCountries(),
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getResultListingColumnConfig(int $sortOrder): array
    {
        $config             = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['class']    = \MageMe\WebForms\Ui\Component\Result\Listing\Column\Field\Region::class;
        $config['bodyTmpl'] = BodyTmpl::HTML;
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $value                            = json_decode((string)$result->getData('field_' . $this->getField()->getId()), true);
        $config                           = $this->getDefaultResultAdminFormConfig();
        $config['type']                   = 'region';
        $config[static::COUNTRY_FIELD_ID] = 'field_' . $this->getField()->getCountryFieldId();
        $config[static::COUNTRY_CODE]     = $this->getField()->getCountryCode();
        $config['region']                 = !empty($value['region']) ? $value['region'] : '';
        $config['region_id']              = !empty($value['region_id']) ? $value['region_id'] : '';
        return $config;
    }

    /**
     * @return array
     */
    private function getCountries(): array
    {
        return $this->countryCollectionFactory->create()->loadByStore(
            $this->getField()->getStoreId())->toOptionArray(__('-- Please Select --'));
    }

    /**
     * @return array
     */
    private function getCountryFields(): array
    {
        /** @var \MageMe\WebForms\Model\Form $form */
        $form    = $this->registry->registry('webforms_form');
        $options = $form->getFieldsAsOptions(Country::class);
        array_unshift($options, ['value' => null, 'label' => __('-- Please Select --')]);
        return $options;
    }
}
