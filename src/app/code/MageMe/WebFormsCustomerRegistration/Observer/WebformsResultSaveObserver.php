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

use MageMe\WebForms\Model;
use MageMe\WebFormsCustomerRegistration\Helper\RegisterHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class WebformsResultSaveObserver implements ObserverInterface
{
    /**
     * @var RegisterHelper
     */
    protected $helperRegister;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     * @param RegisterHelper $helperRegister
     */
    public function __construct(
        Session        $session,
        RegisterHelper $helperRegister
    )
    {
        $this->helperRegister = $helperRegister;
        $this->session        = $session;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function execute(Observer $observer): WebformsResultSaveObserver
    {
        /** @var Model\Result $result */

        $result = $observer->getData('result');

        $form = $result->getForm();

        if (!$form->getCrIsRegisteredOnSubmission()) return $this;

        $groupId  = $form->getCrDefaultGroupId();
        $customer = $this->helperRegister->registerCustomer($result, $groupId);
        if ($customer) {
            $this->session->setCustomerAsLoggedIn($customer);
        }

        return $this;
    }
}
