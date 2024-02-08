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
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Model\Rule\Condition\Customer;

use Magento\Customer\Model\ResourceModel\Attribute\Collection as AttributeCollection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Mageplaza\ZohoCRM\Helper\Sync as HelperSync;

/**
 * Class Condition
 * @package Mageplaza\ZohoCRM\Model\Rule\Condition\Customer
 */
class Condition extends AbstractCondition
{
    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var AttributeCollection
     */
    protected $eavAttributeCollection;

    /**
     * @var HelperSync
     */
    protected $helperSync;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param AttributeCollection $eavAttributeCollection
     * @param Config $eavConfig
     * @param HelperSync $helperSync
     * @param array $data
     */
    public function __construct(
        Context $context,
        AttributeCollection $eavAttributeCollection,
        Config $eavConfig,
        HelperSync $helperSync,
        array $data = []
    ) {
        $this->_eavConfig             = $eavConfig;
        $this->eavAttributeCollection = $eavAttributeCollection;
        $this->helperSync             = $helperSync;

        parent::__construct($context, $data);
        $this->setType(__CLASS__);
    }

    /**
     * @return array
     */
    public function getDefaultAttributes()
    {
        if (!$this->attributes) {
            $attributes = [
                'email',
                'gender',
                'group_id',
                'store_id',
                'created_at',
                'dob',
                'default_billing',
                'default_shipping',
                'taxvat',
            ];

            $this->attributes = $this->eavAttributeCollection
                ->addFieldToFilter('main_table.attribute_code', ['in' => $attributes]);
        }

        return $this->attributes;
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = [];
        foreach ($attributes as $code => $label) {
            $conditions[] = ['value' => $this->getType() . '|' . $code, 'label' => $label];
        }

        return $conditions;
    }

    /**
     * Load condition options for customer attributes
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $customerAttribute = $this->getDefaultAttributes();
        $attributes        = [];

        foreach ($customerAttribute as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        $default = parent::getDefaultOperatorInputByType();

        $default['string'] = ['==', '!=', '{}', '!{}'];
        $default['date']   = ['==', '!=', '>=', '<='];

        return $default;
    }

    /**
     * @return array|mixed
     * @throws LocalizedException
     */
    public function getValueSelectOptions()
    {
        if (!$this->getData('value_select_options')) {
            if ($this->getAttributeObject()->usesSource()) {
                $source = $this->getAttributeObject()->getSource();
                $this->setData('value_select_options', $source->getAllOptions(true));
            } elseif ($this->_isCurrentAttributeDefaultAddress()) {
                $optionsArr = $this->_getOptionsForAttributeDefaultAddress();
                $this->setData('value_select_options', $optionsArr);
            }
        }

        return $this->getData('value_select_options');
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     */
    public function getInputType()
    {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return 'select';
        }

        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'date':
                return $input;
            default:
                return 'string';
        }
    }

    /**
     * @return AbstractAttribute
     * @throws LocalizedException
     */
    public function getAttributeObject()
    {
        return $this->_eavConfig->getAttribute('customer', $this->getAttribute());
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     */
    public function getValueElementType()
    {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return 'select';
        }

        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'date':
                return $input;
            default:
                return 'text';
        }
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function getExplicitApply()
    {
        return $this->getAttributeObject()->getFrontendInput() === 'date';
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
     * @return string
     * @throws LocalizedException
     */
    public function getOperatorElementHtml()
    {
        return $this->_isCurrentAttributeDefaultAddress() ? '' : parent::getOperatorElementHtml();
    }

    /**
     * check if current condition attribute is default billing or shipping address
     * @return bool
     * @throws LocalizedException
     */
    protected function _isCurrentAttributeDefaultAddress()
    {
        $code = $this->getAttributeObject()->getAttributeCode();

        return $code === 'default_billing' || $code === 'default_shipping';
    }

    /**
     * Get options for customer default address attributes value select
     *
     * @return array
     */
    protected function _getOptionsForAttributeDefaultAddress()
    {
        return [
            ['value' => 'is_exists', 'label' => __('exists')],
            ['value' => 'is_not_exists', 'label' => __('does not exist')]
        ];
    }

    /**
     * @return mixed
     * @throws LocalizedException
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
     * @throws LocalizedException
     */
    public function validate(AbstractModel $model)
    {
        if ($this->getAttribute() === 'created_at') {
            $model->setCreatedAt($this->helperSync->formatDate($model->getCreatedAt()));
        }

        if (in_array($this->getData('attribute'), ['default_billing', 'default_shipping'], true)) {
            $value = $model->getData($this->getData('attribute'));

            return $this->getValue() === 'is_exists' ? (bool) $value : !$value;
        }

        return parent::validate($model);
    }
}
