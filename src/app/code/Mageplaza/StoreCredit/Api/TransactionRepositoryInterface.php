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

namespace Mageplaza\StoreCredit\Api;

/**
 * Interface TransactionRepositoryInterface
 * @api
 */
interface TransactionRepositoryInterface
{
    /**
     * Lists Transaction that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria The search criteria.
     *
     * @return \Mageplaza\StoreCredit\Api\Data\TransactionSearchResultInterface Transaction search result
     *     interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);

    /**
     * @param int $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria The search criteria.
     *
     * @return \Mageplaza\StoreCredit\Api\Data\TransactionSearchResultInterface
     */
    public function getTransactionByCustomerId(
        $customerId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    );

    /**
     * Required(customer_id, amount)
     *
     * @param \Mageplaza\StoreCredit\Api\Data\TransactionInterface $data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(\Mageplaza\StoreCredit\Api\Data\TransactionInterface $data);
}
