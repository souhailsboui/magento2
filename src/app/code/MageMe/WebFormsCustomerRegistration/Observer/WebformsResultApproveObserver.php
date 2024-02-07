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

use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use MageMe\WebForms\Model\Result;
use MageMe\WebFormsCustomerRegistration\Helper\RegisterHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManager;

class WebformsResultApproveObserver implements ObserverInterface
{
    /** @var CustomerFactory */
    protected $_customerFactory;

    /** @var StoreManager */
    protected $_storeManager;

    /** @var  RegisterHelper */
    protected $helperRegister;

    /**
     * @param CustomerFactory $customerFactory
     * @param StoreManager $storeManager
     * @param RegisterHelper $helperRegister
     */
    public function __construct(
        CustomerFactory $customerFactory,
        StoreManager    $storeManager,
        RegisterHelper  $helperRegister
    )
    {

        $this->_customerFactory = $customerFactory;
        $this->_storeManager    = $storeManager;
        $this->helperRegister   = $helperRegister;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function execute(Observer $observer): WebformsResultApproveObserver
    {

        /** @var Result $result */
        $result = $observer->getData('result');

        if ($result->getApproved() != ApprovalStatus::STATUS_APPROVED)
            return $this;

        $form = $result->getForm();

        if ($form->getCrIsRegisteredOnApproval()) {
            $groupId = $form->getCrApprovalGroupId();
            $this->helperRegister->registerCustomer($result, $groupId, true);
        }

        return $this;
    }
}
