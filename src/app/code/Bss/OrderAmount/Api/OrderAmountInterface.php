<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderAmount
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderAmount\Api;

/**
 * Interface OrderAmountInterface
 *
 * @package Bss\OrderAmount\Api
 */
interface OrderAmountInterface
{
    /**
     * Set new or update Order Amounts
     *
     * @param mixed $data
     * @return string
     */
    public function setOrderAmount($data);
}
