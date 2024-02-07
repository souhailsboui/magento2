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

namespace MageMe\WebFormsZoho\Config\Options\Crm;

use Exception;
use MageMe\WebFormsZoho\Helper\ZohoHelper;
use Magento\Framework\Data\OptionSourceInterface;

class LeadFields implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;
    /**
     * @var ZohoHelper
     */
    private $zohoHelper;

    /**
     * @param ZohoHelper $zohoHelper
     */
    public function __construct(ZohoHelper $zohoHelper)
    {
        $this->zohoHelper = $zohoHelper;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        if ($this->options) {
            return $this->options;
        }
        try {
            $leadFields   = $this->zohoHelper->getApi()->CRM()->getLeadFields();
            $customFields = [];
            foreach ($leadFields as $leadField) {
                if ($leadField['custom_field']) {
                    $customFields[] = [
                        'label' => __($leadField['field_label']),
                        'value' => $leadField['api_name']
                    ];
                }
            }
            $this->options = $this->getDefaultOptions();
            if ($customFields) {
                $this->options[] = [
                    'label' => __('Custom Fields'),
                    'value' => $customFields,
                ];
            }
        } catch (Exception $exception) {
            return $this->getDefaultOptions();
        }
        return $this->options;

    }

    /**
     * @return array
     */
    private function getDefaultOptions(): array
    {
        return [
            [
                'label' => __('Annual Revenue'),
                'value' => 'Annual_Revenue'
            ],
            [
                'label' => __('City'),
                'value' => 'City'
            ],
            [
                'label' => __('Company'),
                'value' => 'Company'
            ],
            [
                'label' => __('Country'),
                'value' => 'Country'
            ],
//            [
//                'label' => __('Created By'),
//                'value' => 'Created_By'
//            ],
            [
                'label' => __('Description'),
                'value' => 'Description'
            ],
            [
                'label' => __('Email'),
                'value' => 'Email'
            ],
//            [
//                'label' => __('Email Opt Out'),
//                'value' => 'Email_Opt_Out'
//            ],
            [
                'label' => __('Fax'),
                'value' => 'Fax'
            ],
            [
                'label' => __('First Name'),
                'value' => 'First_Name'
            ],
            [
                'label' => __('Industry'),
                'value' => 'Industry'
            ],
            [
                'label' => __('Last Name'),
                'value' => 'Last_Name'
            ],
            [
                'label' => __('Lead Image'),
                'value' => 'Record_Image'
            ],
//            [
//                'label' => __('Lead Owner'),
//                'value' => 'Owner'
//            ],
            [
                'label' => __('Lead Source'),
                'value' => 'Lead_Source'
            ],
//            [
//                'label' => __('Lead Status'),
//                'value' => 'Lead_Status'
//            ],
            [
                'label' => __('Mobile'),
                'value' => 'Mobile'
            ],
//            [
//                'label' => __('Modified By'),
//                'value' => 'Modified_By'
//            ],
            [
                'label' => __('No. of Employees'),
                'value' => 'No_of_Employees'
            ],
            [
                'label' => __('Phone'),
                'value' => 'Phone'
            ],
//            [
//                'label' => __('Rating'),
//                'value' => 'Rating'
//            ],
            [
                'label' => __('Secondary Email'),
                'value' => 'Secondary_Email'
            ],
            [
                'label' => __('Skype ID'),
                'value' => 'Skype_ID'
            ],
            [
                'label' => __('State'),
                'value' => 'State'
            ],
            [
                'label' => __('Street'),
                'value' => 'Street'
            ],
            [
                'label' => __('Title'),
                'value' => 'Designation'
            ],
            [
                'label' => __('Twitter'),
                'value' => 'Twitter'
            ],
            [
                'label' => __('Website'),
                'value' => 'Website'
            ],
            [
                'label' => __('Zip Code'),
                'value' => 'Zip_Code'
            ],
        ];
    }
}