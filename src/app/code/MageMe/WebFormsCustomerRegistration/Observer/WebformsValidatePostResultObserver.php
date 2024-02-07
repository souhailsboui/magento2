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

namespace MageMe\WebFormsCustomerRegistration\Observer;

use MageMe\WebForms\Model\Form;
use MageMe\WebFormsCustomerRegistration\Api\Data\FormInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

class WebformsValidatePostResultObserver implements ObserverInterface
{

    /** @var CustomerFactory */
    protected $_customerFactory;

    /** @var StoreManager */
    protected $_storeManager;

    /** @var UrlInterface */
    protected $urlModel;

    /** @var Context */
    protected $context;

    /** @var CurrentCustomer */
    protected $currentCustomer;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param CustomerFactory $customerFactory
     * @param CurrentCustomer $currentCustomer
     * @param StoreManager $storeManager
     * @param State $state
     * @param Context $context
     */
    public function __construct(
        CustomerFactory $customerFactory,
        CurrentCustomer $currentCustomer,
        StoreManager    $storeManager,
        State           $state,
        Context         $context
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->currentCustomer  = $currentCustomer;
        $this->_storeManager    = $storeManager;
        $this->context          = $context;
        $this->urlModel         = $context->getUrlBuilder();
        $this->state            = $state;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var FormInterface|Form $form */
        $form = $observer->getData('form');

        if (!$form->getCrIsCustomerEmailUnique()) return;

        $validate = $observer->getData('validate');
        $postData = $observer->getData('postData');
        $map      = $form->getCrMap();

        $storeId = $form->getStoreId();
        /** @var Store $store */
        $store               = $this->_storeManager->getStore($storeId);
        $fields_to_fieldsets = $form->getFieldsToFieldsets();
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset)
            foreach ($fieldset['fields'] as $field) {

                if ($field->getIsActive() && $map['customer']['email'] == $field->getId() && $this->state->getAreaCode() == 'frontend') {
                    if ($this->currentCustomer->getCustomerId() && $this->currentCustomer->getCustomer()->getEmail() === $postData['field'][$field->getId()]) return;

                    $customer = $this->_customerFactory->create();
                    $customer->setStore($store);
                    $customer->loadByEmail($postData['field'][$field->getId()]);
                    if ($customer->getId()) {

                        $errors = $validate->getData('errors');
                        $url    = $this->urlModel->getUrl('customer/account/forgotpassword');
                        // @codingStandardsIgnoreStart
                        $message  = __(
                            'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                            $url
                        );
                        $errors[] = $message;
                        $validate->setData('errors', $errors);
                    }
                }
            }
    }
}
