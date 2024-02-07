<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_StoreCredit
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\StoreCredit\Observer;

use Exception;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\StoreCredit\Helper\Email;
use Mageplaza\StoreCredit\Model\CustomerFactory;

/**
 * Class CustomerSaveAfter
 * @package Mageplaza\StoreCredit\Observer
 */
class CustomerSaveAfter implements ObserverInterface
{
    /**
     * @var Email
     */
    protected $helper;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * CustomerSaveAfter constructor.
     *
     * @param CustomerFactory $customerFactory
     * @param Email $helper
     */
    public function __construct(CustomerFactory $customerFactory, Email $helper)
    {
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getCustomer();
        $request = $observer->getEvent()->getRequest();

        $data = $request ?
            $request->getPost('mpstorecredit') :
            ['mp_credit_notification' => $this->helper->isSubscribeByDefault($customer->getStoreId())];

        $customerModel = $this->customerFactory->create();
        $customerModel->load($customer->getId());
        $customerModel->saveAttributeData($customer->getId(), $data ?: []);

        return $this;
    }
}
