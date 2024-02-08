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

namespace Mageplaza\StoreCredit\Plugin\Affiliate;

use Closure;
use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\StoreCredit\Helper\Data as StoreCreditHelper;
use Mageplaza\StoreCredit\Model\Transaction as StoreCreditTransaction;

/**
 * Class Data
 * @package Mageplaza\StoreCredit\Plugin\Affiliate\Helper
 */
class Withdraw
{
    /**
     * @var StoreCreditHelper
     */
    private $helper;

    /**
     * @var StoreCreditTransaction
     */
    private $transaction;

    /**
     * Data constructor.
     *
     * @param StoreCreditHelper $helper
     * @param StoreCreditTransaction $transaction
     */
    public function __construct(StoreCreditHelper $helper, StoreCreditTransaction $transaction)
    {
        $this->helper      = $helper;
        $this->transaction = $transaction;
    }

    /**
     * @param \Mageplaza\Affiliate\Model\Withdraw $subject
     * @param Closure $proceed
     * @param array $data
     * @param \Magento\Customer\Model\Customer
     * @throws LocalizedException
     */
    public function aroundProcessStoreCredit(
        \Mageplaza\Affiliate\Model\Withdraw $subject,
        Closure $proceed,
        array $data,
        \Magento\Customer\Model\Customer $customer
    ) {
        if (!$this->helper->isEnabled()) {
            return $proceed($data, $customer);
        }

        try {
            $this->transaction->createTransaction(
                StoreCreditHelper::ACTION_EARNING_AFFILIATE,
                $customer,
                new DataObject($data)
            );
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
