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

namespace Mageplaza\StoreCredit\Model\ResourceModel\Transaction;

use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Mageplaza\StoreCredit\Api\Data\TransactionSearchResultInterface;

/**
 * Class Collection
 * @package Mageplaza\StoreCredit\Model\ResourceModel\Transaction
 */
class Collection extends AbstractCollection implements TransactionSearchResultInterface
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_store_credit_transaction_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'store_credit_transaction_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Mageplaza\StoreCredit\Model\Transaction',
            'Mageplaza\StoreCredit\Model\ResourceModel\Transaction'
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->join(
            ['customer' => $this->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            ['email']
        );

        return $this;
    }

    /**
     * @param $field
     * @param null $condition
     *
     * @return AbstractCollection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'created_at') {
            $field = 'main_table.created_at';
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
