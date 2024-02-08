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

namespace Mageplaza\ZohoCRM\Observer\Order;

use Magento\Framework\Event\Observer;
use Mageplaza\ZohoCRM\Observer\AbstractQueue;

/**
 * Class OrderDeleteBefore
 * @package Mageplaza\ZohoCRM\Observer\Order
 */
class OrderDeleteBefore extends AbstractQueue
{
    /**
     * @param Observer $observer
     *
     * @return AbstractQueue|void
     */
    public function executeAction(Observer $observer)
    {
        $order = $observer->getEvent()->getDataObject();
        $order->setInvoiceCollectionBeforeDelete($order->getInvoiceCollection());
    }
}
