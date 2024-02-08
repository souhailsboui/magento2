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

namespace Mageplaza\StoreCredit\Block\Account\Dashboard;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\StoreCredit\Block\Account\Dashboard;

/**
 * Class Balance
 * @package Mageplaza\StoreCredit\Block\Account\Dashboard
 */
class Balance extends Dashboard
{
    /**
     * @return int
     * @throws LocalizedException
     */
    public function getBalance()
    {
        return $this->helper->getAccountHelper()->getConvertAndFormatBalance();
    }
}
