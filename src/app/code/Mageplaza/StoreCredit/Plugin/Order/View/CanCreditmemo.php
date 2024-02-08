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

namespace Mageplaza\StoreCredit\Plugin\Order\View;

use Magento\Sales\Model\Order;
use Mageplaza\StoreCredit\Helper\Calculation;

/**
 * Class CanCreditmemo
 * @package Mageplaza\StoreCredit\Plugin\Order\View
 */
class CanCreditmemo
{
    /**
     * @var Calculation
     */
    private $helper;

    /**
     * CanCreditmemo constructor.
     *
     * @param Calculation $helper
     */
    public function __construct(Calculation $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Order $subject
     */
    public function beforeCanCreditmemo(Order $subject)
    {
        if (in_array($subject->getState(), [
                Order::STATE_PROCESSING,
                Order::STATE_COMPLETE,
                Order::STATE_CLOSED
            ]) && $subject->getMpStoreCreditDiscount()) {
            $value = $this->validateQty($subject) && $this->validateCredit($subject);

            $subject->setForcedCanCreditmemo($value);
        }
    }

    /**
     * @param Order $subject
     *
     * @return bool
     */
    public function validateCredit($subject)
    {
        $credit = $subject->getMpStoreCreditDiscount();

        foreach ($subject->getCreditmemosCollection() as $creditmemo) {
            $credit -= $creditmemo->getMpStoreCreditDiscount();
        }

        return floatval($credit) > 0 || $this->helper->isAllowRefundExchange($subject->getStoreId());
    }

    /**
     * @param Order $subject
     *
     * @return bool
     */
    public function validateQty($subject)
    {
        foreach ($subject->getItems() as $item) {
            if ($item->getQtyRefunded() < $item->getQtyOrdered()) {
                return true;
            }
        }

        return false;
    }
}
