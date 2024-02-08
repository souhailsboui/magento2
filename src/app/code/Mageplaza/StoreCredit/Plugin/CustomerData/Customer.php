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

namespace Mageplaza\StoreCredit\Plugin\CustomerData;

use Mageplaza\StoreCredit\Helper\Calculation;

/**
 * Class Customer
 * @package Mageplaza\StoreCredit\Plugin\CustomerData
 */
class Customer
{
    /**
     * @var Calculation
     */
    protected $helper;

    /**
     * Customer constructor.
     *
     * @param Calculation $helper
     */
    public function __construct(Calculation $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        $quote = $this->helper->getCheckoutSession()->getQuote();
        $creditData = [];

        if ($this->helper->isEnabled($quote->getStoreId())) {
            $creditData = [
                'isSpendingCredit' => !!floatval($quote->getMpStoreCreditSpent()),
                'balance' => $this->helper->getAccountHelper()->getBalance(),
                'convertedBalance' => $this->helper->convertPrice(
                    $this->helper->getAccountHelper()->getBalance(),
                    true,
                    false
                ),
                'isEnabledFor' => $this->helper->isEnabledForCustomer()
            ];
        }

        return array_merge($result, $creditData);
    }
}
