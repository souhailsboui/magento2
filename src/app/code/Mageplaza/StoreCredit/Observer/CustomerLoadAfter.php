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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;
use Mageplaza\StoreCredit\Helper\Data;
use Mageplaza\StoreCredit\Model\CustomerFactory;

/**
 * Class CustomerLoadAfter
 * @package Mageplaza\StoreCredit\Observer
 */
class CustomerLoadAfter implements ObserverInterface
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param CustomerFactory $customerFactory
     * @param Data $helper
     */
    public function __construct(CustomerFactory $customerFactory, Data $helper)
    {
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
    }

    /**
     * After load observer for quote
     *
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($customer instanceof AbstractModel) {
            $customerModel = $this->customerFactory->create();
            $customerModel->load($customer->getId());
            $customerModel->attachAttributeData($customer);
        }

        return $this;
    }
}
