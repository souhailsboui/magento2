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
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\ZohoCRM\Model\Rule\Condition\Invoice;

use Magento\Framework\Model\AbstractModel;
use Mageplaza\ZohoCRM\Model\Rule\Condition\Order\Condition as OrderCondition;

/**
 * Class Condition
 * @package Mageplaza\ZohoCRM\Model\Rule\Condition\Invoice
 */
class Condition extends OrderCondition
{
    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'grand_total'         => __('Grand Total'),
            'subtotal'            => __('Subtotal'),
            'total_qty'           => __('Total Quantity'),
            'order_date'          => __('Order Date'),
            'order_status'        => __('Order Status'),
            'store_id'            => __('Purchased From'),
            'tax_amount'          => 'Tax Amount',
            'payment_method'      => __('Payment Method'),
            'shipping_method'     => __('Shipping Method'),
            'shipping_postcode'   => __('Shipping Postcode'),
            'shipping_region'     => __('Shipping Region'),
            'shipping_region_id'  => __('Shipping State/Province'),
            'shipping_country_id' => __('Shipping Country'),
            'billing_postcode'    => __('Billing Postcode'),
            'billing_region'      => __('Billing Region'),
            'billing_region_id'   => __('Billing State/Province'),
            'billing_country_id'  => __('Billing Country'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'subtotal':
            case 'grand_total':
            case 'tax_amount':
            case 'total_qty':
                return 'numeric';
            case 'order_date':
                return 'date';

            case 'shipping_method':
            case 'payment_method':
            case 'shipping_country_id':
            case 'billing_country_id':
            case 'shipping_region_id':
            case 'billing_region_id':
            case 'order_status':
            case 'order_purchase_from':
                return 'select';
        }

        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'created_at':
            case 'order_date':
                return 'date';
            case 'shipping_method':
            case 'payment_method':
            case 'shipping_country_id':
            case 'billing_country_id':
            case 'shipping_region_id':
            case 'status':
            case 'order_status':
            case 'order_purchase_from':
            case 'store_id':
                return 'select';
        }

        return 'text';
    }

    /**
     * Validate Rule Condition
     *
     * @param AbstractModel $model
     *
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        $order = $model->getOrder();
        switch ($this->getAttribute()) {
            case 'order_status':
                $model->setOrderStatus($order->getStatus());

                break;

            case 'order_date':
                $model->setOrderDate($order->getCreatedAt());

                break;
        }

        return parent::validate($model);
    }
}
