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

namespace Mageplaza\StoreCredit\Block\Sales\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;

/**
 * Class Discount
 * @package Mageplaza\StoreCredit\Block\Sales\Order
 */
class Discount extends Template
{
    /**
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        if ($source->getMpStoreCreditDiscount() > 0.0001) {
            $parent->addTotal(new DataObject(
                [
                    'code' => 'mp_store_credit_spent',
                    'value' => -$source->getMpStoreCreditDiscount(),
                    'base_value' => -$source->getMpStoreCreditBaseDiscount(),
                    'label' => __('Store Credit')
                ]
            ), 'tax');
        }

        return $this;
    }
}
