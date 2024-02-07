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

namespace MageMe\WebFormsCustomerRegistration\Plugin\Customer\Block\Form;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Block\Form;
use Magento\Customer\Block\Form\Register as RegisterForm;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 *
 */
class Register
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param RegisterForm $registerForm
     * @throws LocalizedException
     */
    public function beforeToHtml(RegisterForm $registerForm)
    {
        if ($this->_scopeConfig->getValue('webforms/registration_form/replace', ScopeInterface::SCOPE_STORE)) {

            $registerForm->setTemplate('MageMe_WebFormsCustomerRegistration::form/register.phtml');

            $block = $registerForm->getLayout()->createBlock(Form::class, 'webforms.register.form', [
                'data' => [
                    FormInterface::ID => $this->_scopeConfig->getValue('webforms/registration_form/webform', ScopeInterface::SCOPE_STORE),
                ]
            ]);
            $registerForm->append($block);
        }
    }
}
