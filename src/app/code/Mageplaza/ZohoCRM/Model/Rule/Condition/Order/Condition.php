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

namespace Mageplaza\ZohoCRM\Model\Rule\Condition\Order;

use Exception;
use Magento\Directory\Model\Config\Source\Allregion;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\Model\AbstractModel;
use Magento\Payment\Model\Config\Source\Allmethods as PaymentMethods;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order\Invoice;
use Magento\Shipping\Model\Config\Source\Allmethods;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Ui\Component\Listing\Column\Store\Options as Store;
use Mageplaza\ZohoCRM\Helper\Sync as HelperSync;
use Mageplaza\ZohoCRM\Model\Source\CustomerGroup;

/**
 * Class Condition
 * @package Mageplaza\ZohoCRM\Model\Rule\Condition\Order
 */
class Condition extends AbstractCondition
{
    /**
     * @var Country
     */
    protected $_directoryCountry;

    /**
     * @var Allregion
     */
    protected $_directoryRegions;

    /**
     * @var Allmethods
     */
    protected $_shippingMethods;

    /**
     * @var PaymentMethods
     */
    protected $_paymentMethods;

    /**
     * @var Status
     */
    protected $orderStatus;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HelperSync
     */
    protected $helperSync;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param Country $directoryCountry
     * @param Allregion $directoryRegions
     * @param Allmethods $shippingMethods
     * @param PaymentMethods $paymentMethods
     * @param StoreManagerInterface $storeManager
     * @param Status $orderStatus
     * @param Store $store
     * @param CustomerGroup $customerGroup
     * @param HelperSync $helperSync
     * @param array $data
     */
    public function __construct(
        Context $context,
        Country $directoryCountry,
        Allregion $directoryRegions,
        Allmethods $shippingMethods,
        PaymentMethods $paymentMethods,
        StoreManagerInterface $storeManager,
        Status $orderStatus,
        Store $store,
        CustomerGroup $customerGroup,
        HelperSync $helperSync,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_directoryCountry = $directoryCountry;
        $this->_directoryRegions = $directoryRegions;
        $this->_shippingMethods  = $shippingMethods;
        $this->_paymentMethods   = $paymentMethods;
        $this->orderStatus       = $orderStatus;
        $this->store             = $store;
        $this->customerGroup     = $customerGroup;
        $this->storeManager      = $storeManager;
        $this->helperSync        = $helperSync;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();

        $attributes = [
            'grand_total'         => __('Grand Total'),
            'base_subtotal'       => __('Subtotal'),
            'total_qty_ordered'   => __('Total Quantity'),
            'weight'              => __('Total Weight'),
            'status'              => __('Order Status'),
            'created_at'          => __('Order Date'),
            'customer_group_id'   => __('Customer Group'),
            'store_id'            => __('Purchased From'),
            'tax_amount'          => __('Tax Amount'),
            'payment_method'      => __('Payment Method'),
            'shipping_method'     => __('Shipping Method'),
            'shipping_amount'     => __('Shipping Amount'),
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
     * @return AbstractCondition
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal':
            case 'weight':
            case 'total_qty_ordered':
            case 'tax_amount':
                return 'numeric';

            case 'shipping_method':
            case 'status':
            case 'payment_method':
            case 'shipping_country_id':
            case 'billing_country_id':
            case 'shipping_region_id':
            case 'billing_region_id':
            case 'customer_group_id':
            case 'store_id':
                return 'select';
            case 'created_at':
                return 'date';
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
                return 'date';
            case 'shipping_method':
            case 'payment_method':
            case 'shipping_country_id':
            case 'billing_country_id':
            case 'shipping_region_id':
            case 'status':
            case 'billing_region_id':
            case 'customer_group_id':
            case 'store_id':
                return 'select';
        }

        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'billing_country_id':
                case 'shipping_country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'billing_region_id':
                case 'shipping_region_id':
                    $options = $this->_directoryRegions->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = $this->_shippingMethods->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->_paymentMethods->toOptionArray();

                    break;
                case 'order_status':
                case 'status':
                    $options = $this->orderStatus->toOptionArray();
                    unset($options[0]);
                    break;
                case 'store_id':
                    $options = $this->getStoreOptions();

                    break;
                case 'customer_group_id':
                    $options = $this->customerGroup->toOptionArray();
                    break;
                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }

    /**
     * @return array
     */
    public function getStoreOptions()
    {
        $options = [];
        $stores  = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $options[] = [
                'label' => $store->getName(),
                'value' => $store->getId()
            ];
        }

        return $options;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getValue()
    {
        if (!$this->getIsValueParsed() && in_array($this->getAttribute(), ['created_at', 'order_date'], true)) {
            $this->setValue($this->helperSync->formatDate($this->getData('value')));
            $this->setIsValueParsed(true);
        }

        return $this->getData('value');
    }

    /**
     * Check if attribute value should be explicit
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        if (in_array($this->getAttribute(), ['created_at', 'order_date'], true)) {
            return true;
        }

        return false;
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
        switch ($this->getAttribute()) {
            case 'payment_method':
                $payment = $model->getPayment();
                if ($model instanceof Invoice) {
                    $payment = $model->getOrder()->getPayment();
                }
                $model->setPaymentMethod($payment->getMethod());

                break;

            case 'shipping_postcode':
                if ($model->getShippingAddress()) {
                    $model->setShippingPostcode($model->getShippingAddress()->getPostcode());
                }

                break;
            case 'shipping_region':
                if ($model->getShippingAddress()) {
                    $model->setShippingRegion($model->getShippingAddress()->getRegion());
                }

                break;
            case 'shipping_region_id':
                if ($model->getShippingAddress()) {
                    $model->setShippingRegionId($model->getShippingAddress()->getRegionId());
                }

                break;
            case 'shipping_country_id':
                if ($model->getShippingAddress()) {
                    $model->setShippingCountryId($model->getShippingAddress()->getCountryId());
                }

                break;
            case 'billing_postcode':
                $model->setBillingPostcode($model->getBillingAddress()->getPostcode());

                break;
            case 'billing_region':
                $model->setBillingRegion($model->getBillingAddress()->getRegion());

                break;
            case 'billing_region_id':
                $model->setBillingRegionId($model->getBillingAddress()->getRegionId());

                break;
            case 'billing_country_id':
                $model->setBillingCountryId($model->getBillingAddress()->getCountryId());

                break;
            case 'created_at':
                $model->setCreatedAt($this->helperSync->formatDate($model->getCreatedAt()));

                break;
            case 'customer_group_id':
                $customerGroupId = $model->getData('customer_group_id');

                if ($customerGroupId === '0') {
                    // rule not validate when value is 0. Change value equal -1 to validate
                    $model->setData('customer_group_id', '-1');
                }

                break;
        }

        return parent::validate($model);
    }
}
