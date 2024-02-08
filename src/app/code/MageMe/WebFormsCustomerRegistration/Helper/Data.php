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

namespace MageMe\WebFormsCustomerRegistration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 *
 */
class Data extends AbstractHelper
{
    /**
     * @return array[]
     */
    public function getCustomerAttributes(): array
    {
        return [
            ['label' => __('Name Prefix'), 'code' => 'prefix'],
            ['label' => __('First Name'), 'code' => 'firstname', 'required' => true],
            ['label' => __('Middle Name/Initial'), 'code' => 'middlename'],
            ['label' => __('Last Name'), 'code' => 'lastname', 'required' => true],
            ['label' => __('Name Suffix'), 'code' => 'suffix'],
            ['label' => __('Email'), 'code' => 'email', 'required' => true],
            ['label' => __('Date of Birth'), 'code' => 'dob'],
            ['label' => __('Tax/VAT Number'), 'code' => 'taxvat'],
            ['label' => __('Gender'), 'code' => 'gender'],
            ['label' => __('Password'), 'code' => 'password'],
        ];
    }

    /**
     * @return array[]
     */
    public function getBillingAddressAttributes(): array
    {
        return [
            ['label' => __('Name Prefix'), 'code' => 'prefix'],
            ['label' => __('First Name'), 'code' => 'firstname', 'required' => true],
            ['label' => __('Middle Name/Initial'), 'code' => 'middlename'],
            ['label' => __('Last Name'), 'code' => 'lastname', 'required' => true],
            ['label' => __('Name Suffix'), 'code' => 'suffix'],
            ['label' => __('Company'), 'code' => 'company'],
            ['label' => __('Street Line 1'), 'code' => 'street1', 'required' => true],
            ['label' => __('Street Line 2'), 'code' => 'street2'],
            ['label' => __('City'), 'code' => 'city', 'required' => true],
            ['label' => __('Country'), 'code' => 'country_id', 'required' => true],
            ['label' => __('State/Province'), 'code' => 'region'],
            ['label' => __('Zip/Postal Code'), 'code' => 'postcode', 'required' => true],
            ['label' => __('Phone Number'), 'code' => 'telephone', 'required' => true],
            ['label' => __('Fax'), 'code' => 'fax'],
            ['label' => __('VAT Number'), 'code' => 'vat_id'],
        ];
    }

    /**
     * @return array[]
     */
    public function getShippingAddressAttributes(): array
    {
        return [
            ['label' => __('Name Prefix'), 'code' => 'prefix'],
            ['label' => __('First Name'), 'code' => 'firstname', 'required' => true],
            ['label' => __('Middle Name/Initial'), 'code' => 'middlename'],
            ['label' => __('Last Name'), 'code' => 'lastname', 'required' => true],
            ['label' => __('Name Suffix'), 'code' => 'suffix'],
            ['label' => __('Company'), 'code' => 'company'],
            ['label' => __('Street Line 1'), 'code' => 'street1', 'required' => true],
            ['label' => __('Street Line 2'), 'code' => 'street2'],
            ['label' => __('City'), 'code' => 'city', 'required' => true],
            ['label' => __('Country'), 'code' => 'country_id', 'required' => true],
            ['label' => __('State/Province'), 'code' => 'region'],
            ['label' => __('Zip/Postal Code'), 'code' => 'postcode', 'required' => true],
            ['label' => __('Phone Number'), 'code' => 'telephone', 'required' => true],
            ['label' => __('Fax'), 'code' => 'fax'],
        ];
    }

    /**
     * @return array[]
     */
    public function getAdditionalAttributes(): array
    {
        return [
            ['label' => __('Allow remote shopping assistance'), 'code' => 'assistance_allowed'],
        ];
    }

}
