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
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Mageplaza\StoreCredit\Helper\Calculation as Helper;

/**
 * Class CreateSpendCreditTransaction
 * @package Mageplaza\StoreCredit\Observer
 */
class CreateSpendCreditTransaction implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * StoreCreditConvertData constructor.
     *
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($amount = floatval($quote->getMpStoreCreditBaseDiscount())) {
            if ($customerId = $quote->getCustomerId()) {
                /** Add spending credit transaction */
                $this->helper->addTransaction(Helper::ACTION_SPENDING_ORDER, $customerId, -$amount, $order);
            }
        }
        return $this;
    }
}
