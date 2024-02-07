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

namespace Mageplaza\StoreCredit\Plugin\Customer\Grid;

use Magento\Customer\Model\ResourceModel\Grid\Collection as CustomerCollection;

/**
 * Class Collection
 * @package Mageplaza\StoreCredit\Plugin\Customer\Grid
 */
class Collection
{
    /**
     * @param CustomerCollection $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetSelect(
        CustomerCollection $subject,
        $result
    ) {
        if ($result && !$subject->getFlag('is_mageplaza_store_credit_customer_joined')) {
            $table = $subject->getResource()->getTable('mageplaza_store_credit_customer');
            $result->joinLeft(
                ['mp_store_credit_customer' => $table],
                'mp_store_credit_customer.customer_id = main_table.entity_id'
            );
            $subject->setFlag('is_mageplaza_store_credit_customer_joined', true);
        }

        return $result;
    }
}
