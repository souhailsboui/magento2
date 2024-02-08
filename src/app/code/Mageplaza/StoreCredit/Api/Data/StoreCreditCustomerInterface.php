<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\StoreCredit\Api\Data;

/**
 * Interface StoreCreditCustomerInterface
 * @package Mageplaza\StoreCredit\Api\Data
 */
interface StoreCreditCustomerInterface
{
    const CUSTOMER_ID               = 'customer_id';
    const STORE_CREDIT_BALANCE      = 'mp_credit_balance';
    const STORE_CREDIT_NOTIFICATION = 'mp_credit_notification';

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCustomerId($value);

    /**
     * @return string
     */
    public function getMpCreditBalance();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setMpCreditBalance($value);

    /**
     * @return int
     */
    public function getMpCreditNotification();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMpCreditNotification($value);
}
