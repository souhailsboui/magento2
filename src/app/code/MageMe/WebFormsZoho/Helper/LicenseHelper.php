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

namespace MageMe\WebFormsZoho\Helper;


class LicenseHelper extends \MageMe\WebForms\Helper\LicenseHelper
{
    /**
     * @inheritdoc
     */
    public function getConfigSection(): string {
        return 'webformszoho';
    }

    /**
     * @inheritdoc
     */
    public function getModuleName(): string {
        return 'MageMe_WebFormsZoho';
    }

    /**
     * @inheritdoc
     */
    public function getModuleTitle() {
        return __('Zoho Integration Add-on');
    }
}
