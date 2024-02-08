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

namespace Mageplaza\StoreCredit\Model;

use Magento\Sales\Model\Order\Creditmemo as SalesCreditmemo;
use Mageplaza\StoreCredit\Api\Data\CreditmemoInterface;

/**
 * Class Creditmemo
 * @package Mageplaza\StoreCredit\Model
 */
class Creditmemo extends SalesCreditmemo implements CreditmemoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMpStoreCreditDiscount()
    {
        return $this->getData(self::MP_STORE_CREDIT_DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpStoreCreditDiscount($value)
    {
        return $this->setData(self::MP_STORE_CREDIT_DISCOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMpStoreCreditBaseDiscount()
    {
        return $this->getData(self::MP_STORE_CREDIT_BASE_DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMpStoreCreditBaseDiscount($value)
    {
        return $this->setData(self::MP_STORE_CREDIT_BASE_DISCOUNT, $value);
    }
}
