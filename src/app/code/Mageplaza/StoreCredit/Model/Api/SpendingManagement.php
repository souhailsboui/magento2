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

namespace Mageplaza\StoreCredit\Model\Api;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Model\Quote;
use Mageplaza\StoreCredit\Api\SpendingManagementInterface;
use Mageplaza\StoreCredit\Model\CustomerFactory;
use Magento\Framework\Exception\InputException;

/**
 * Class SpendingManagement
 * @package Mageplaza\StoreCredit\Model\Api
 */
class SpendingManagement implements SpendingManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * Cart total repository.
     *
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * SpendingManagement constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartTotalRepositoryInterface $cartTotalRepository,
        CustomerFactory $customerFactory
    ) {
        $this->cartRepository      = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->customerFactory     = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function spend($cartId, $amount)
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        if ($customerId = $quote->getCustomerId()) {
            $storeCreditCustomer = $this->customerFactory->create()->load($customerId);
            if (!$storeCreditCustomer->getCustomerId()
                || $amount < 0
                || $storeCreditCustomer->getMpCreditBalance() < $amount) {
                throw new InputException(__('Amount is not allowed'));
            }
        }

        $quote->setMpStoreCreditSpent($amount);

        $this->cartRepository->save($quote->collectTotals());

        return $this->cartTotalRepository->get($quote->getId());
    }
}
