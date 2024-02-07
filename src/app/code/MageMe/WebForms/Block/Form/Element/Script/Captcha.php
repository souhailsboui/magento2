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

namespace MageMe\WebForms\Block\Form\Element\Script;

use MageMe\WebForms\Block\Form\Element\AbstractElement;
use MageMe\WebForms\Config\Options\Captcha\Type;
use MageMe\WebForms\Helper\Captcha\AbstractCaptcha;
use MageMe\WebForms\Helper\Captcha\Hcaptcha;
use MageMe\WebForms\Helper\Captcha\Recaptcha;
use MageMe\WebForms\Helper\Captcha\Turnstile;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

class Captcha extends AbstractElement
{
    /**
     * @return string
     */
    public function getLocale(): string
    {
        try {
            $storeId = $this->_storeManager->getStore()->getId();
        } catch (NoSuchEntityException $ex) {
            return '';
        }
        return $this->_scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return AbstractCaptcha|Hcaptcha|Recaptcha|Turnstile|mixed
     */
    public function getCaptcha() {
        return $this->getForm()->getCaptcha()->getCaptcha();
    }

    /**
     * @inheritDoc
     */
    public function getTemplate()
    {
        if (!$this->getForm() || !$this->getForm()->getCaptcha()) {
            return parent::getTemplate();
        }
        switch ($this->getForm()->getCaptcha()->getCaptchaType()) {
            case Type::HCAPTCHA:
            {
                $this->_template = self::TEMPLATE_PATH . 'script/captcha/hCaptcha.phtml';
                break;
            }
            case Type::TURNSTILE:
            {
                $this->_template = self::TEMPLATE_PATH . 'script/captcha/turnstile.phtml';
                break;
            }
            default:
            {
                $this->_template = self::TEMPLATE_PATH . 'script/captcha/reCaptcha.phtml';
            }
        }
        return parent::getTemplate();
    }
}
