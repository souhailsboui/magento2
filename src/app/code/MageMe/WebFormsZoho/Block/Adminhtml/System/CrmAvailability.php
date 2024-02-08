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

namespace MageMe\WebFormsZoho\Block\Adminhtml\System;

use Magento\Framework\Data\Form\Element\AbstractElement;

class CrmAvailability extends AbstractConfigField
{
    const SCOPES = '<div>ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,ZohoCRM.Files.CREATE,ZohoCRM.users.READ</div>';

    protected $_template = 'MageMe_WebFormsZoho::system/availability.phtml';

    /**
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl('webformszoho/system/crmscopecheck');
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->isConfigured() ?  $this->_toHtml() : __('Please configure Zoho with scopes:') . self::SCOPES;
    }
}