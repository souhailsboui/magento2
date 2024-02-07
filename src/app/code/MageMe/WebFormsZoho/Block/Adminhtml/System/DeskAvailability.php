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

class DeskAvailability extends AbstractConfigField
{
    const SCOPES = '<div>Desk.basic.READ,Desk.settings.READ,Desk.tickets.CREATE,Desk.tickets.UPDATE,Desk.contacts.READ,Desk.contacts.CREATE</div>';

    protected $_template = 'MageMe_WebFormsZoho::system/availability.phtml';

    /**
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl('webformszoho/system/deskscopecheck');
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->isConfigured() ?  $this->_toHtml() : __('Please configure Zoho with scopes:') . self::SCOPES;
    }
}