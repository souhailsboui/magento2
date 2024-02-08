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

namespace MageMe\WebFormsCustomerRegistration\Plugin\WebForms\Helper\ConvertVersion;


use MageMe\WebFormsCustomerRegistration\Api\Data\FormInterface;

class FormConverter
{
    const REGISTER_ON_SUBMISSION = 'cr_register_on_submisison';
    const UNIQUE_CUSTOMER_EMAIL = 'cr_unique_customer_email';
    const DEFAULT_GROUP = 'cr_default_group';
    const REGISTER_ON_APPROVAL = 'cr_register_on_approval';
    const APPROVAL_GROUP = 'cr_approval_group';
    const SEND_DEFAULT_EMAIL = 'cr_send_default_email';
    const MAP = 'cr_map';

    /**
     * @param \MageMe\WebForms\Helper\ConvertVersion\FormConverter $formConverter
     * @param array $data
     * @param array $oldData
     * @return array
     */
    public function afterConvertV2Data(\MageMe\WebForms\Helper\ConvertVersion\FormConverter $formConverter, array $data, array $oldData): array
    {
        $data[FormInterface::CR_IS_REGISTERED_ON_SUBMISSION] = $oldData[self::REGISTER_ON_SUBMISSION] ?? false;
        $data[FormInterface::CR_IS_CUSTOMER_EMAIL_UNIQUE] = $oldData[self::UNIQUE_CUSTOMER_EMAIL] ?? false;
        $data[FormInterface::CR_DEFAULT_GROUP_ID] = $oldData[self::DEFAULT_GROUP] ?? null;
        $data[FormInterface::CR_IS_REGISTERED_ON_APPROVAL] = $oldData[self::REGISTER_ON_APPROVAL] ?? false;
        $data[FormInterface::CR_APPROVAL_GROUP_ID] = $oldData[self::APPROVAL_GROUP] ?? null;
        $data[FormInterface::CR_IS_DEFAULT_NOTIFICATION_ENABLED] = $oldData[self::SEND_DEFAULT_EMAIL] ?? false;
        $data[FormInterface::CR_MAP] = $oldData[self::MAP] ?? [];
        return $data;
    }
}
