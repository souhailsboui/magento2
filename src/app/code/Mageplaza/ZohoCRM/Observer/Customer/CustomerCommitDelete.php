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

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\ZohoCRM\Model\Queue;
use Mageplaza\ZohoCRM\Model\Source\MagentoObject;
use Mageplaza\ZohoCRM\Observer\AbstractQueue;

/**
 * Class CustomerCommitDelete
 * @package Mageplaza\ZohoCRM\Observer\Customer
 */
class CustomerCommitDelete extends AbstractQueue
{
    /**
     * @param Observer $observer
     *
     * @return AbstractQueue|void
     * @throws NoSuchEntityException
     */
    public function executeAction(Observer $observer)
    {
        /**
         * @var Queue $queue
         */
        $queue    = $this->queueFactory->create();
        $customer = $observer->getEvent()->getDataObject();
        $queue->addDeleteObjectToQueue($customer, MagentoObject::CUSTOMER);
    }
}
