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

use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Customer
 * @package Mageplaza\StoreCredit\Model\ResourceModel
 */
class Customer extends AbstractDb
{
    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_store_credit_customer', 'customer_id');
    }

    /**
     * @param array $entities
     *
     * @return array
     */
    public function attachDataToCustomerGrid($entities)
    {
        $items = [];
        $itemIds = [];
        foreach ($entities as $entity) {
            $itemIds[] = $entity['entity_id'];
            $items[$entity['entity_id']] = new DataObject();
        }

        if ($itemIds) {
            $mainTable = $this->getTable('mageplaza_store_credit_customer');
            $ceTable = $this->getTable('customer_entity');

            $select = $this->getConnection()->select()->from(
                $this->getTable('mageplaza_store_credit_customer')
            )->where(
                'customer_id IN (?)',
                $itemIds
            )->join(
                $this->getTable('customer_entity'),
                $mainTable . '.customer_id = ' . $ceTable . '.entity_id',
                'store_id'
            );

            $customers = $this->getConnection()->fetchAll($select);
            foreach ($customers as $customer) {
                $items[$customer['customer_id']]->addData($customer);
            }
        }

        return $items;
    }
}
