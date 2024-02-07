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
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Observer\Customer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;
use Mageplaza\ZohoCRM\Observer\AbstractQueue;

/**
 * Class CustomerAddressSaveAfter
 *
 * @package Mageplaza\ZohoCRM\Observer\Customer
 */
class CustomerAddressSaveAfter extends AbstractQueue
{
    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var $customerAddress Address */
        $customerAddress = $observer->getEvent()->getDataObject();
        $customer        = $customerAddress->getCustomer();
        $customer->load($customer->getId());
        $origData = $customerAddress->getCustomOrigData();
        if ($customer->getZohoEntity()) {
            $this->helperSync->updateObject($origData, $customerAddress, ZohoModule::ACCOUNT);
        } elseif (!$customer->isObjectNew() && !$customer->hasQueueSave()) {
            $this->helperSync->addObjectToQueue(ZohoModule::ACCOUNT, $customer);
        }

        if ($customer->getZohoLeadEntity()) {
            $this->helperSync->updateObject($origData, $customerAddress, ZohoModule::LEAD);
        } elseif (!$customer->isObjectNew() && !$customer->hasQueueSave()) {
            $this->helperSync->addObjectToQueue(ZohoModule::LEAD, $customer);
        }

        if ($customer->getZohoContactEntity()) {
            $this->helperSync->updateObject($origData, $customerAddress, ZohoModule::CONTACT);
        } elseif (!$customer->isObjectNew() && !$customer->hasQueueSave()) {
            $this->helperSync->addObjectToQueue(ZohoModule::CONTACT, $customer);
        }
    }
}
