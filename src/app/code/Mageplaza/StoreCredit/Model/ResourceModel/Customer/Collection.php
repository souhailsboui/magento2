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

namespace Mageplaza\StoreCredit\Model\ResourceModel\Customer;

use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Mageplaza\StoreCredit\Api\Data\StoreCreditCustomerSearchResultInterface;

/**
 * Class Collection
 * @package Mageplaza\StoreCredit\Model\ResourceModel\Customer
 */
class Collection extends AbstractCollection implements StoreCreditCustomerSearchResultInterface
{
    /**
     * @type string
     */
    protected $_idFieldName = 'customer_id';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\StoreCredit\Model\Customer', 'Mageplaza\StoreCredit\Model\ResourceModel\Customer');
    }
}
