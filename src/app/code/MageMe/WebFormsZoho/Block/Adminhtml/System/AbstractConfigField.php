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

use MageMe\WebFormsZoho\Helper\ZohoHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

abstract class AbstractConfigField extends Field
{
    /**
     * @var array
     */
    protected $fieldConfig = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * License constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context             $context,
        array               $data = [])
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element): string
    {
        $this->fieldConfig = $element->getData('field_config');
        $this->fieldConfig['html_id'] = $element->getHtmlId();
        return parent::render($element);
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->scopeConfig->getValue(ZohoHelper::CONFIG_CLIENT_ID) &&
            $this->scopeConfig->getValue(ZohoHelper::CONFIG_CLIENT_SECRET) &&
            $this->scopeConfig->getValue(ZohoHelper::CONFIG_CODE);
    }

    /**
     * @return string
     */
    public function getHtmlId(): string
    {
        return $this->fieldConfig['html_id'] ?? '';
    }

    /**
     * @return string
     */
    abstract public function getAjaxUrl(): string;
}