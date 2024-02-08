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

use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\StoreCredit\Api\Data\TransactionInterface;
use Mageplaza\StoreCredit\Api\Data\TransactionSearchResultInterface;
use Mageplaza\StoreCredit\Api\Data\TransactionSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\StoreCredit\Api\TransactionRepositoryInterface;
use Mageplaza\StoreCredit\Helper\Account as HelperAccount;
use Mageplaza\StoreCredit\Helper\Data;

/**
 * Class TransactionRepository
 * @package Mageplaza\StoreCredit\Model
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var TransactionFactory
     */
    protected $storeCreditTransactionFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var HelperAccount
     */
    protected $helperAccount;

    /**
     * TransactionRepository constructor.
     *
     * @param Data $helperData
     * @param SearchResultFactory $searchResultFactory
     * @param TransactionFactory $storeCreditTransactionFactory
     * @param CustomerFactory $customerFactory
     * @param HelperAccount $helperAccount
     */
    public function __construct(
        Data $helperData,
        SearchResultFactory $searchResultFactory,
        TransactionFactory $storeCreditTransactionFactory,
        CustomerFactory $customerFactory,
        HelperAccount $helperAccount
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->helperData = $helperData;
        $this->storeCreditTransactionFactory = $storeCreditTransactionFactory;
        $this->customerFactory = $customerFactory;
        $this->helperAccount = $helperAccount;
    }

    /**
     * Find entities by criteria
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     *
     * @return TransactionSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        $searchResult = $this->searchResultFactory->create();

        $transactionCollection = $this->helperData->processGetList($searchResult, $searchCriteria);
        foreach ($transactionCollection->getItems() as $item) {
            $item->setAmount($this->helperAccount->getConvertAndFormatBalance($item->getAmount()));
            $item->setBalance($this->helperAccount->getConvertAndFormatBalance($item->getBalance()));
        }

        return $transactionCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function getTransactionByCustomerId($customerId, SearchCriteriaInterface $searchCriteria = null)
    {
        $searchResult = $this->searchResultFactory->create()->addFieldToFilter('customer_id', $customerId);
        $transactionCollection = $this->helperData->processGetList($searchResult, $searchCriteria);
        foreach ($transactionCollection->getItems() as $item) {
            $item->setAmount($this->helperAccount->getConvertAndFormatBalance($item->getAmount(), $customerId));
            $item->setBalance($this->helperAccount->getConvertAndFormatBalance($item->getBalance(), $customerId));
        }

        return $transactionCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function create(TransactionInterface $data)
    {
        if (!$data->getCustomerId()) {
            throw new InputException(__('Customer id required'));
        }

        if ($data->getAmount() <= 0) {
            throw new InputException(__('Amount must be greater than zero'));
        }

        $customer = $this->customerFactory->create()->load($data->getCustomerId());
        if (!$customer->getId()) {
            throw new NoSuchEntityException(__('Customer doesn\'t exist'));
        }

        try {
            $data = [
                'action' => $data->getAction(),
                'customer_id' => $data->getCustomerId(),
                'amount' => $data->getAmount(),
                'customer_note' => $data->getCustomerNote(),
                'admin_note' => $data->getAdminNote()
            ];
            /** @var Transaction $transaction */
            $transaction = $this->storeCreditTransactionFactory->create();
            $transaction->createTransaction(Data::ACTION_ADMIN_UPDATE, $customer, new DataObject($data));
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }
}
