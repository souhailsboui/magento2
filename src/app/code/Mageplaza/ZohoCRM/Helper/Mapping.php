<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as ProductAttributeCollection;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Collection as CustomerAddressAttributeCollection;
use Magento\Customer\Model\ResourceModel\Attribute\Collection as CustomerAttributeCollection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\ZohoCRM\Model\ResourceModel\Sync as ResourceModelSync;
use Mageplaza\ZohoCRM\Model\Source\MagentoObject;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;

/**
 * Class Data
 * @package Mageplaza\ZohoCRM\Helper
 */
class Mapping extends AbstractData
{
    /**
     * Match options in {{ }}
     */
    const PATTERN_OPTIONS = '/{{([a-zA-Z_]{0,50})(.*?)}}/si';

    /***
     * @var ZohoModule
     */
    protected $zohoOption;

    /**
     * @var ResourceModelSync
     */
    protected $resourceSync;

    /**
     * @var ProductAttributeCollection
     */
    protected $productAttributeCollection;

    /**
     * @var CustomerAttributeCollection
     */
    protected $customerAttributeCollection;

    /**
     * @var CustomerAddressAttributeCollection
     */
    protected $customerAddressAttributeCollection;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Mapping constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ZohoModule $zohoModule
     * @param ResourceModelSync $resourceSync
     * @param ProductAttributeCollection $productAttributeCollection
     * @param CustomerAttributeCollection $customerAttributeCollection
     * @param CustomerAddressAttributeCollection $customerAddressAttributeCollection
     * @param Escaper $escaper
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ZohoModule $zohoModule,
        ResourceModelSync $resourceSync,
        ProductAttributeCollection $productAttributeCollection,
        CustomerAttributeCollection $customerAttributeCollection,
        CustomerAddressAttributeCollection $customerAddressAttributeCollection,
        Escaper $escaper,
        Data $helperData
    ) {
        $this->zohoOption                         = $zohoModule;
        $this->resourceSync                       = $resourceSync;
        $this->productAttributeCollection         = $productAttributeCollection;
        $this->customerAttributeCollection        = $customerAttributeCollection;
        $this->customerAddressAttributeCollection = $customerAddressAttributeCollection;
        $this->escaper                            = $escaper;
        $this->helperData                         = $helperData;
        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param string $value
     *
     * @return array|mixed
     */
    public function matchData($value)
    {
        preg_match_all(self::PATTERN_OPTIONS, $value, $matches);
        if ($matches && isset($matches[1])) {
            return $matches[1];
        }

        return [];
    }

    /**
     * @return array
     */
    public function getZohoObject()
    {
        return [
            'product'  => $this->getProductFieldsZoho(),
            'campaign' => $this->getCampaignFieldsZoho(),
            'account'  => $this->getAccountFieldsZoho(),
            'contact'  => $this->getContactFieldsZoho(),
            'invoice'  => $this->getInvoiceFieldZoho(),
            'lead'     => $this->getLeadFieldsZoho(),
            'order'    => $this->getOrderFieldsZoho()
        ];
    }

    /**
     * @param string $object
     *
     * @return string
     */
    public function createMappingFields($object)
    {
        $mappings = $this->getZohoObject()[$object];

        return $this->createFields($mappings);
    }

    /**
     * @param array $mappings
     *
     * @return string
     */
    public function createFields($mappings)
    {
        $html = '';
        foreach ($mappings as $key => $mappingData) {
            $html .= $this->createRow($key, $mappingData);
        }

        return $html;
    }

    /**
     * @param string $key
     * @param array $mappingData
     *
     * @return string
     */
    public function createRow($key, $mappingData)
    {
        $html = '<tr>';
        $html .= $this->createLabel($key, $mappingData);
        $html .= $this->createInput($key, 'value', $mappingData);
        $html .= $this->createInput($key, 'default', $mappingData);
        $html .= $this->createInput($key, 'description', $mappingData);
        $html .= '</tr>';

        return $html;
    }

    /**
     * @param string $key
     * @param array $value
     *
     * @return string
     */
    public function createLabel($key, $value)
    {
        return '<td>
                    <label class="admin__field-label mapping-label" for="' . $key . '">
                        <span>' . $value['label'] . '</span>
                    </label>
                </td>';
    }

    /**
     * @param string $key
     * @param string $name
     * @param array $data
     *
     * @return string
     */
    public function createInput($key, $name, $data)
    {
        $dataInit = '';

        $nameInput = 'sync[mapping][' . $key . '][' . $name . ']';
        $comment   = '';
        if ($name === 'default' || $name === 'value') {
            $comment = '<div class="mp-field-comment">' . __('Accept %1 value.', $data['type']) . '</div>';
        }

        if ($name === 'default' && $data['type'] === 'date') {
            $dataInit = 'data-mage-init="' . $this->escaper->escapeHtml($this->initDate()) . '"';
        }

        $button = '';
        if ($name === 'value') {
            $button = $this->createButton($key, $data);
            $html   = '<td>
                     <div class="admin__field-control control" style="position: relative" >
                        <textarea id="' . $key . '-' . $name . '" 
                        name="' . $nameInput . '"
                        title="' . $data['label'] . '"
                        class="input-text admin__control-text" ' . $dataInit . '
                        style="width:100%;resize: none;min-height: 35px;height:35px"
                        >' . $data[$name] . '</textarea>
                        ' . $comment . $button . '
                     </div>
                </td>';
        } else {
            $html = '<td>
                     <div class="admin__field-control control" style="position: relative" >
                        <input id="' . $key . '-' . $name . '" 
                        name="' . $nameInput . '"
                        title="' . $data['label'] . '"
                        type="text"
                        value="' . $data[$name] . '"
                        class="input-text admin__control-text" ' . $dataInit . '
                        style="width:100%"
                        >
                        ' . $comment . $button . '
                     </div>
                </td>';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function initDate()
    {
        return self::jsonEncode(
            [
                'calendar' => [
                    'dateFormat'  => 'yyyy-MM-dd',
                    'showsTime'   => false,
                    'timeFormat'  => null,
                    'buttonImage' => null,
                    'buttonText'  => __('Select Date'),
                    'disabled'    => null,
                    'minDate'     => null,
                    'maxDate'     => null,
                ],
            ]
        );
    }

    /**
     * @param string $key
     * @param array $data
     *
     * @return string
     */
    public function createButton($key, $data)
    {
        $title     = __('Insert Variable...');
        $typeName  = 'sync[mapping][' . $key . '][type]';
        $typeValue = $data['type'];

        return '<button id="insert_variable" 
                            title="' . $title . '" 
                            target="' . $key . '" 
                            type="button"
                            style="position: absolute;top: 0;right: -45px;" 
                            class="insert_variable">
                                <span>...</span>
                    </button>
                    <input type="hidden" name="' . $typeName . '" value="' . $typeValue . '" />
                ';
    }

    /**
     * @param \Mageplaza\ZohoCRM\Model\Sync $sync
     *
     * @return string
     */
    public function getMappingFieldsByRule($sync)
    {
        $mapping     = Data::jsonDecode($sync->getMapping());
        $mappingData = [];
        $zohoObject  = $this->getZohoObject()[$sync->getZohoModule()];

        foreach ($mapping as $key => $dataField) {
            $dataField['label'] = $zohoObject[$key]['label'];
            $dataField['type']  = $zohoObject[$key]['type'];
            $mappingData[$key]  = $dataField;
        }

        return $this->createFields($mappingData);
    }

    /**
     * @return array
     */
    public function getMappingObject()
    {
        $options = $this->zohoOption->toOptionArray();

        return [
            MagentoObject::CUSTOMER     => [$options[0], $options[5], $options[6]],
            MagentoObject::PRODUCT      => [$options[1]],
            MagentoObject::ORDER        => [$options[2]],
            MagentoObject::INVOICE      => [$options[3]],
            MagentoObject::CATALOG_RULE => [$options[4]]
        ];
    }

    /**
     * @param string $type
     * @param bool $isJsonEncode
     *
     * @return array|string
     */
    public function getDefaultVariable($type, $isJsonEncode = false)
    {
        $data = [];
        switch ($type) {
            case ZohoModule::PRODUCT:
                $data = $this->getDefaultProductVariable();
                break;
            case ZohoModule::ORDER:
                $data = $this->getDefaultOrderVariable();
                break;
            case ZohoModule::CAMPAIGN:
                $data = $this->getDefaultCatalogRuleVariable();
                break;
            case ZohoModule::INVOICE:
                $data = $this->getDefaultInvoiceVariable();
                break;
            case ZohoModule::CONTACT:
            case ZohoModule::ACCOUNT:
            case ZohoModule::LEAD:
                $data = $this->getDefaultCustomerVariable();
                break;
            default:
        }

        return $isJsonEncode ? self::jsonEncode($data) : $data;
    }

    /**
     *  =========================================== ZOHO FIELDS ====================================================
     */

    /**
     * @return array
     */
    public function getProductFieldsZoho()
    {
        return [
            'Product_Category'    => [
                'label'       => __('Product Category'),
                'value'       => '{{category_ids}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Product_Code'        => [
                'label'       => __('Product Code'),
                'value'       => '{{sku}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Product_Active'      => [
                'label'       => __('Product Active'),
                'value'       => '{{status}}',
                'default'     => '',
                'description' => '',
                'type'        => 'boolean'
            ],
            'Product_Name'        => [
                'label'       => __('Product Name'),
                'value'       => '{{name}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Sales_Start_Date'    => [
                'label'       => __('Sales Start Date'),
                'value'       => '{{news_from_date}}',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Sales_End_Date'      => [
                'label'       => __('Sales End Date'),
                'value'       => '{{news_to_date}}',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Support_Start_Date'  => [
                'label'       => __('Support Start Date'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Support_Expiry_Date' => [
                'label'       => __('Support End Date'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Manufacturer'        => [
                'label'       => __('Manufacturer'),
                'value'       => '{{manufacturer}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Unit_Price'          => [
                'label'       => __('Unit Price'),
                'value'       => '{{price}}',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Taxable'             => [
                'label'       => __('Taxable'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'boolean'
            ],
            'Tax'                 => [
                'label'       => __('Tax'),
                'value'       => '{{tax_class_id}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Commission_Rate'     => [
                'label'       => __('Commission Rate'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Usage_Unit'          => [
                'label'       => __('Usage Unit'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Qty_in_Stock'        => [
                'label'       => __('Quantity in Stock'),
                'value'       => '{{qty}}',
                'default'     => '',
                'description' => '',
                'type'        => 'int'
            ],
            'Qty_Ordered'         => [
                'label'       => __('Qty Ordered'),
                'value'       => '{{qty}}',
                'default'     => '',
                'description' => '',
                'type'        => 'int'
            ],
            'Reorder_Level'       => [
                'label'       => __('Reorder Level'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'int'
            ],
            'Qty_in_Demand'       => [
                'label'       => __('Qty In Demand'),
                'value'       => '{{qty_increments}}',
                'default'     => '',
                'description' => '',
                'type'        => 'int'
            ],
            'Description'         => [
                'label'       => __('Description'),
                'value'       => '{{description}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
        ];
    }

    /**
     * @return array
     */
    public function getCampaignFieldsZoho()
    {
        $fields = [
            'Campaign_Name'     => [
                'label'       => __('Campaign Name'),
                'value'       => '{{name}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Status'            => [
                'label'       => __('Status'),
                'value'       => '{{is_active}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Start_Date'        => [
                'label'       => __('Start Date'),
                'value'       => '{{from_date}}',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'End_Date'          => [
                'label'       => __('End Date'),
                'value'       => '{{to_date}}',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Expected_Revenue'  => [
                'label'       => __('Expected Revenue'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Budgeted_Cost'     => [
                'label'       => __('Budgeted Cost'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Actual_Cost'       => [
                'label'       => __('Actual Cost'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Expected_Response' => [
                'label'       => __('Expected Response'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'int'
            ],
            'Numbers_Sent'      => [
                'label'       => __('Numbers sent'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'int'
            ],
            'Description'       => [
                'label'       => __('Description'),
                'value'       => '{{description}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
        ];

        if ($this->helperData->isEnterprise()) {
            unset($fields['Start_Date'], $fields['End_Date']);
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getAccountFieldsZoho()
    {
        $zohoFields = [
            'Account_Name'   => [
                'label'       => __('Account Name'),
                'value'       => '{{name}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Account_Number' => [
                'label'       => __('Account Number'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Account_Site'   => [
                'label'       => __('Account Site'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Parent_Account' => [
                'label'       => __('Parent Account'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string',
            ],
            'Account_Type'   => [
                'label'       => __('Account Type'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Industry'       => [
                'label'       => __('Industry'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Annual_Revenue' => [
                'label'       => __('Annual Revenue'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Phone'          => [
                'label'       => __('Phone'),
                'value'       => '{{billing_telephone}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Fax'            => [
                'label'       => __('Fax'),
                'value'       => '{{billing_fax}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Website'        => [
                'label'       => __('Website'),
                'value'       => '{{website}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Ticker_Symbol'  => [
                'label'       => __('Ticker Symbol'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Ownership'      => [
                'label'       => __('Ownership'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Employees'      => [
                'label'       => __('Employees'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'sic_code'       => [
                'label'       => __('SIC Code'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Description'    => [
                'label'       => __('Description'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
        ];

        return array_merge($zohoFields, $this->getAddressFields());
    }

    /**
     * @return array
     */
    public function getAddressFields()
    {
        return [
            'Billing_Street'   => [
                'label'       => __('Billing Street'),
                'value'       => '{{billing_street}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Billing_City'     => [
                'label'       => __('Billing City'),
                'value'       => '{{billing_city}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Billing_Code'     => [
                'label'       => __('Billing Code'),
                'value'       => '{{billing_postcode}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Billing_Country'  => [
                'label'       => __('Billing Country'),
                'value'       => '{{billing_country_id}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Billing_State'    => [
                'label'       => __('Billing State'),
                'value'       => '{{billing_region}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Shipping_Street'  => [
                'label'       => __('Shipping Street'),
                'value'       => '{{shipping_street}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Shipping_City'    => [
                'label'       => __('Shipping City'),
                'value'       => '{{shipping_city}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Shipping_State'   => [
                'label'       => __('Shipping State'),
                'value'       => '{{shipping_region}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Shipping_Code'    => [
                'label'       => __('Shipping Code'),
                'value'       => '{{shipping_postcode}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Shipping_Country' => [
                'label'       => __('Shipping Country'),
                'value'       => '{{shipping_country_id}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
        ];
    }

    /**
     * @return array
     */
    public function getContactFieldsZoho()
    {
        return [
            'Email'           => [
                'label'       => __('Email'),
                'value'       => '{{email}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'First_Name'      => [
                'label'       => __('First Name'),
                'value'       => '{{firstname}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Last_Name'       => [
                'label'       => __('Last Name'),
                'value'       => '{{lastname}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Phone'           => [
                'label'       => __('Phone'),
                'value'       => '{{billing_telephone}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string',
            ],
            'Mobile'          => [
                'label'       => __('Mobile'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Department'      => [
                'label'       => __('Department'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Other_Phone'     => [
                'label'       => __('Other Phone'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Assistant'       => [
                'label'       => __('Assistant'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Lead_Source'     => [
                'label'       => __('Lead Source'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Title'           => [
                'label'       => __('Title'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Home_Phone'      => [
                'label'       => __('Home Phone'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Fax'             => [
                'label'       => __('Fax'),
                'value'       => '{{billing_fax}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Date_of_Birth'   => [
                'label'       => __('Date of Birth'),
                'value'       => '{{dob}}',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Asst_Phone'      => [
                'label'       => __('Asst Phone'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Email_Opt_Out'   => [
                'label'       => __('Email Opt Out'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'boolean'
            ],
            'Skype_ID'        => [
                'label'       => __('Skype ID'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Secondary_Email' => [
                'label'       => __('Secondary Email'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Twitter'         => [
                'label'       => __('Twitter'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Mailing_Street'  => [
                'label'       => __('Mailing Street'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Mailing_City'    => [
                'label'       => __('Mailing City'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Mailing_State'   => [
                'label'       => __('Mailing State'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Mailing_Zip'     => [
                'label'       => __('Mailing Zip'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Mailing_Country' => [
                'label'       => __('Mailing Country'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Other_Street'    => [
                'label'       => __('Other Street'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Other_City'      => [
                'label'       => __('Other City'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Other_State'     => [
                'label'       => __('Other State'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Other_Zip'       => [
                'label'       => __('Other Zip'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Other_Country'   => [
                'label'       => __('Other Country'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Description'     => [
                'label'       => __('Description'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
        ];
    }

    /**
     * @return array
     */
    public function getLeadFieldsZoho()
    {
        return [
            'First_Name'      => [
                'label'       => __('First Name'),
                'value'       => '{{firstname}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Last_Name'       => [
                'label'       => __('Last Name'),
                'value'       => '{{lastname}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Title'           => [
                'label'       => __('Title'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Phone'           => [
                'label'       => __('Phone'),
                'value'       => '{{billing_telephone}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Mobile'          => [
                'label'       => __('Mobile'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Lead_Source'     => [
                'label'       => __('Lead Source'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Industry'        => [
                'label'       => __('Industry'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Annual_Revenue'  => [
                'label'       => __('Annual Revenue'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Email_Opt_Out'   => [
                'label'       => __('Email Opt Out'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'boolean'
            ],
            'Company'         => [
                'label'       => __('Company'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Email'           => [
                'label'       => __('Email'),
                'value'       => '{{email}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Fax'             => [
                'label'       => __('Fax'),
                'value'       => '{{billing_fax}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Website'         => [
                'label'       => __('Website'),
                'value'       => '{{website}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Lead_Status'     => [
                'label'       => __('Lead Status'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'No_of_Employees' => [
                'label'       => __('No. of Employees'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Rating'          => [
                'label'       => __('Rating'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],

            'Skype_ID'        => [
                'label'       => __('Skype ID'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Twitter'         => [
                'label'       => __('Twitter'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Secondary_Email' => [
                'label'       => __('Secondary Email'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'City'            => [
                'label'       => __('City'),
                'value'       => '{{billing_city}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Zip_Code'        => [
                'label'       => __('Zip Code'),
                'value'       => '{{billing_postcode}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Street'          => [
                'label'       => __('Street'),
                'value'       => '{{billing_street}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'State'           => [
                'label'       => __('State'),
                'value'       => '{{billing_region_id}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Country'         => [
                'label'       => __('Country'),
                'value'       => '{{billing_country}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Description'     => [
                'label'       => __('Description'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getOrderFieldsZoho()
    {
        $fields = [
            'Subject'              => [
                'label'       => __('Subject'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'So_Number'            => [
                'label'       => __('SO Number'),
                'value'       => '{{increment_id}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'PO_Number'            => [
                'label'       => __('PO Number'),
                'value'       => '{{increment_id}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'PO_Date'              => [
                'label'       => __('PO Date'),
                'value'       => '{{created_at}}',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Tracking_Number'      => [
                'label'       => __('Tracking Number'),
                'value'       => '{{increment_id}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Status'               => [
                'label'       => __('Status'),
                'value'       => '{{status}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Due_Date'             => [
                'label'       => __('Dua Date'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Carrier'              => [
                'label'       => __('Carrier'),
                'value'       => '{{shipping_method}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Sales_Commission'     => [
                'label'       => __('Sales Commission'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Excise_Duty'          => [
                'label'       => __('Excise Duty'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Sub_Total'            => [
                'label'       => __('Sub Total'),
                'value'       => '{{subtotal}}',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Discount'             => [
                'label'       => __('Discount'),
                'value'       => '{{discount_amount}}',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Tax'                  => [
                'label'       => __('Tax'),
                'value'       => '{{tax_amount}}',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Adjustment'           => [
                'label'       => __('Adjustment'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Grand_Total'          => [
                'label'       => __('Grand Total'),
                'value'       => '{{grand_total}}',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Terms_And_Conditions' => [
                'label'       => __('Terms and Conditions'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Description'          => [
                'label'       => __('Description'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
        ];

        return array_merge($fields, $this->getAddressFields());
    }

    /**
     * @return array
     */
    public function getInvoiceFieldZoho()
    {
        $fields = [
            'Subject'             => [
                'label'       => __('Subject'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Invoice_Date'        => [
                'label'       => __('Invoice Date'),
                'value'       => '{{created_at}}',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Due_Date'            => [
                'label'       => __('Due date'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'date'
            ],
            'Invoice_Number'      => [
                'label'       => __('Invoice Number'),
                'value'       => '{{increment_id}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Grand_Total'         => [
                'label'       => __('Grand_Total'),
                'value'       => '{{grand_total}}',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Sales_Commission'    => [
                'label'       => __('Sales Commission'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Discount'            => [
                'label'       => __('Discount'),
                'value'       => '{{discount_amount}}',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Tax'                 => [
                'label'       => __('Tax'),
                'value'       => '{{tax_amount}}',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Sub_Total'           => [
                'label'       => __('Sub Total'),
                'value'       => '{{sub_total}}',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Purchase_Order'      => [
                'label'       => __('Purchase Order'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Excise_Duty'         => [
                'label'       => __('Excise Duty'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Status'              => [
                'label'       => __('Status'),
                'value'       => '{{status}}',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Adjustment'          => [
                'label'       => __('Adjustment'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'float'
            ],
            'Term_And_Conditions' => [
                'label'       => __('Term And Conditions'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
            'Description'         => [
                'label'       => __('Description'),
                'value'       => '',
                'default'     => '',
                'description' => '',
                'type'        => 'string'
            ],
        ];

        return array_merge($fields, $this->getAddressFields());
    }

    /**
     *  =========================================== DEFAULT VALUES ====================================================
     */

    /**
     * @return array
     */
    public function getDefaultProductVariable()
    {
        $productAttributes = $this->productAttributeCollection->getItems();

        return [
            'label' => __('Product'),
            'value' => $this->getDataAttribute($productAttributes)
        ];
    }

    /**
     * @param object $attributes
     * @param null $type
     * @param null $prefix
     *
     * @return array
     */
    public function getDataAttribute($attributes, $type = null, $prefix = null)
    {
        $data  = [];
        $types = ['media_image', 'weee', 'swatch_visual', 'swatch_text', 'gallery', 'texteditor'];
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getFrontendInput(), $types, true)) {
                continue;
            }

            $attributeCode            = $attribute->getAttributeCode();
            $customerIgnoreAttributes = [
                'disable_auto_group_change',
                'vat_is_valid',
                'vat_request_date',
                'vat_request_id',
                'vat_request_success'
            ];

            if ($type === 'customer' && in_array($attributeCode, $customerIgnoreAttributes, true)) {
                continue;
            }

            $prefixLabel = '';
            if ($prefix) {
                $attributeCode = $prefix . '_' . $attributeCode;
                $prefixLabel   = ucfirst($prefix) . ' ';
            }

            $label = $prefixLabel . $attribute->getFrontendLabel();
            if ($attribute->getAttributeCode() === 'region_id') {
                $label .= ' ID';
            }

            $data[] = [
                'value' => '{{' . $attributeCode . '}}',
                'label' => $label
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getDefaultCustomerVariable()
    {
        $customerAttributes         = $this->getDataAttribute(
            $this->customerAttributeCollection->getItems(),
            'customer'
        );
        $addressAttributes          = $this->customerAddressAttributeCollection->getItems();
        $customerBillingAttributes  = $this->getDataAttribute($addressAttributes, 'customer', 'billing');
        $customerShippingAttributes = $this->getDataAttribute($addressAttributes, 'customer', 'shipping');

        return [
            'label' => __('Customer'),
            'value' => array_merge($customerAttributes, $customerBillingAttributes, $customerShippingAttributes)
        ];
    }

    /**
     * @return array
     */
    public function getDefaultInvoiceVariable()
    {
        return [
            'label' => __('Invoice'),
            'value' => $this->resourceSync->getFieldTable('sales_invoice')
        ];
    }

    /**
     * @return array
     */
    public function getDefaultCatalogRuleVariable()
    {
        return [
            'label' => 'Catalog Rules',
            'value' => [
                [
                    'label' => __('Rule name'),
                    'value' => '{{name}}'
                ],
                [
                    'label' => __('Description'),
                    'value' => '{{description}}'
                ],
                [
                    'label' => __('Status'),
                    'value' => '{{status}}'
                ],
                [
                    'label' => __('From Date'),
                    'value' => '{{from_date}}'
                ],
                [
                    'label' => __('To Date'),
                    'value' => '{{to_date}}'
                ],
                [
                    'label' => __('Priority'),
                    'value' => '{{priority}}'
                ],
                [
                    'label' => __('Discount Amount'),
                    'value' => '{{discount_amount}}'
                ],
                [
                    'label' => __('Apply'),
                    'value' => '{{simple_action}}'
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public function getDefaultOrderVariable()
    {
        return [
            'label' => __('Order'),
            'value' => $this->resourceSync->getFieldTable('sales_order')
        ];
    }
}
