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

namespace Mageplaza\StoreCredit\Model\ResourceModel;

use Exception;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Mageplaza\StoreCredit\Model\CustomerFactory;

/**
 * Class Transaction
 * @package Mageplaza\StoreCredit\Model\ResourceModel
 */
class Transaction extends AbstractDb
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * Transaction constructor.
     *
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        string $connectionName = null
    ) {
        $this->customerFactory = $customerFactory;

        parent::__construct($context, $connectionName);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_store_credit_transaction', 'transaction_id');
    }

    /**
     * @param \Mageplaza\StoreCredit\Model\Transaction $transaction
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return mixed
     * @throws Exception
     */
    public function saveTransaction($transaction, $customer)
    {
        $this->beginTransaction();
        try {
            $transaction->save();
            $this->customerFactory->create()->saveAttributeData($customer->getId(), $customer->getData());
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $transaction;
    }
}
