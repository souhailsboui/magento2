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

namespace MageMe\WebFormsCustomerRegistration\Plugin\WebForms\Model\ResourceModel\Form;

use MageMe\WebForms\Model\ResourceModel\Form;
use MageMe\WebFormsCustomerRegistration\Api\Data\FormInterface;

class GetSerializableFields
{
    /**
     * @param Form $form
     * @param array $serializableFields
     * @return array
     */
    public function afterGetSerializableFields(Form $form, array $serializableFields): array
    {
        $serializableFields[FormInterface::CR_MAP] = [
            $form::SERIALIZE_OPTION_SERIALIZED => FormInterface::CR_MAP_SERIALIZED,
            $form::SERIALIZE_OPTION_DEFAULT_DESERIALIZED => []
        ];
        return $serializableFields;
    }

}
