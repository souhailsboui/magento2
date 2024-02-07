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

namespace Mageplaza\ZohoCRM\Model\Rule\Condition\CatalogRule;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Mageplaza\ZohoCRM\Helper\Data;
use Mageplaza\ZohoCRM\Helper\Sync as HelperSync;
use Mageplaza\ZohoCRM\Model\Source\CustomerGroup;

/**
 * Class Condition
 * @package Mageplaza\ZohoCRM\Model\Rule\Condition\CatalogRule
 */
class Condition extends AbstractCondition
{
    /**
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var HelperSync
     */
    protected $helperSync;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param CustomerGroup $customerGroup
     * @param Data $helperData
     * @param HelperSync $helperSync
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerGroup $customerGroup,
        Data $helperData,
        HelperSync $helperSync,
        array $data = []
    ) {
        $this->customerGroup = $customerGroup;
        $this->helperData    = $helperData;
        $this->helperSync    = $helperSync;
        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'from_date'          => __('From'),
            'to_date'            => __('To'),
            'discount_amount'    => __('Discount Amount'),
            'customer_group_ids' => 'Customer Group'
        ];

        if ($this->helperData->isEnterprise()) {
            unset($attributes['from_date'], $attributes['to_date']);
        }

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
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        $default = parent::getDefaultOperatorInputByType();

        $default['date']        = ['==', '!=', '>=', '<='];
        $default['multiselect'] = ['{}', '!{}'];

        return $default;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'from_date':
            case 'to_date':
                return 'date';
            case 'customer_group_ids':
                return 'multiselect';
        }

        return 'string';
    }

    /**
     * Check if attribute value should be explicit
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        return $this->getInputType() === 'date';
    }

    /**
     * Get attribute value input element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if (in_array($this->getInputType(), ['date', 'multiselect'], true)) {
            return $this->getInputType();
        }

        return 'text';
    }

    /**
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $options = $this->getAttribute() === 'customer_group_ids' ? $this->customerGroup->toOptionArray() : [];

            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if ($this->getInputType() === 'date' && !$this->getIsValueParsed()) {
            $this->setValue($this->helperSync->formatDate($this->getData('value')));
            $this->setIsValueParsed(true);
        }

        return $this->getData('value');
    }

    /**
     * @param AbstractModel $model
     *
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        if ($this->getAttribute() === 'customer_group_ids') {
            $customerGroupIds = $model->getData('customer_group_ids');

            if (isset($customerGroupIds[0]) && $customerGroupIds[0] === '0') {
                // rule not validate when value is 0. Change value equal -1 to validate
                $customerGroupIds[0] = -1;
            }

            $model->setData('customer_group_ids', $customerGroupIds);
        }

        return parent::validate($model);
    }
}
