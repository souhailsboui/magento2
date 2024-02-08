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

namespace Mageplaza\ZohoCRM\Observer;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Mageplaza\ZohoCRM\Helper\Sync;

/**
 * Class AbstractModelSaveBefore
 * @package Mageplaza\ZohoCRM\Observer
 */
class AbstractModelSaveBefore implements ObserverInterface
{
    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Sync
     */
    protected $helperSync;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * AbstractModelSaveBefore constructor.
     *
     * @param AddressFactory $addressFactory
     * @param CustomerFactory $customerFactory
     * @param Order $order
     * @param Invoice $invoice
     * @param Sync $helperSync
     */
    public function __construct(
        AddressFactory $addressFactory,
        CustomerFactory $customerFactory,
        Order $order,
        Invoice $invoice,
        Sync $helperSync
    ) {
        $this->addressFactory  = $addressFactory;
        $this->customerFactory = $customerFactory;
        $this->order           = $order;
        $this->helperSync      = $helperSync;
        $this->invoice         = $invoice;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /**
         * @var AbstractModel $object
         */
        $object         = $observer->getEvent()->getDataObject();
        $customOrigData = '';

        if (!$object->getId()) {
            //isObjectNew can't use on this case
            $object->setIsNewRecord(true);
        } elseif ($object instanceof Address) {
            $customOrigData = $this->addressFactory->create()->load($object->getId());
        } elseif ($object instanceof Customer) {
            $customOrigData = $this->customerFactory->create()->load($object->getId());
        } elseif ($object instanceof Order) {
            $customOrigData = $this->order->load($object->getId());
        } elseif ($object instanceof Invoice) {
            $customOrigData = $this->invoice->load($object->getId());
        }

        if ($customOrigData) {
            $object->setCustomOrigData($customOrigData);
        }
    }
}
