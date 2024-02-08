<?php

namespace Meetanshi\ShippingRules\Model\Rule\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Directory\Model\Config\Source\Allregion;
use Magento\Rule\Model\Condition\Context;

class Address extends AbstractCondition
{
    protected $directoryCountry;

    protected $directoryAllregion;

    public function __construct(Context $context, Country $directoryCountry, Allregion $directoryAllregion, array $data = [])
    {
        parent::__construct($context, $data);
        $this->directoryCountry = $directoryCountry;
        $this->directoryAllregion = $directoryAllregion;
    }

    public function loadAttributeOptions()
    {
        $attributes = [
            'package_value' => __('Subtotal'),
            'package_value_with_discount' => __('Subtotal with discount'),
            'package_qty' => __('Total Items Quantity'),
            'package_weight' => __('Total Weight'),
            'dest_postcode' => __('Shipping Postcode'),
            'dest_region_id' => __('Shipping State/Province'),
            'dest_country_id' => __('Shipping Country'),
            'dest_city' => __('Shipping City'),
            'dest_street' => __('Shipping Address Line'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'dest_country_id':
            case 'dest_region_id':
                return 'select';
        }
        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'dest_country_id':
                    $options = $this->directoryCountry->toOptionArray();
                    break;

                case 'dest_region_id':
                    $options = $this->directoryAllregion->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    public function getOperatorSelectOptions()
    {
        $operators = $this->getOperatorOption();
        if ($this->getAttribute() == 'dest_street') {
            $operators = [
                '{}' => __('contains'),
                '!{}' => __('does not contain'),
                '{%' => __('starts from'),
                '%}' => __('ends with'),
            ];
        }

        $type = $this->getInputType();
        $operator = [];
        $operatorByType = $this->getOperatorByInputType();
        foreach ($operators as $k => $v) {
            if (!$operatorByType || in_array($k, $operatorByType[$type])) {
                $operator[] = ['value' => $k, 'label' => $v];
            }
        }
        return $operator;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'package_value':
            case 'package_weight':
            case 'package_qty':
                return 'numeric';

            case 'dest_country_id':
            case 'dest_region_id':
                return 'select';
        }
        return 'string';
    }

    public function getDefaultOperatorInputByType()
    {
        $op = parent::getDefaultOperatorInputByType();
        $op['string'][] = '{%';
        $op['string'][] = '%}';
        return $op;
    }

    public function getDefaultOperatorOptions()
    {
        $op = parent::getDefaultOperatorOptions();
        $op['{%'] = __('starts from');
        $op['%}'] = __('ends with');

        return $op;
    }

    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        if (is_string($validatedValue)) {
            $validatedValue = strtoupper($validatedValue);
        }

        $value = $this->getValueParsed();
        if (is_string($value)) {
            $value = strtoupper($value);
        }

        $operator = $this->getOperatorForValidate();

        if ($this->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;
        switch ($operator) {
            case '{%':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = substr($validatedValue, 0, strlen($value)) == $value;
                }
                break;
            case '%}':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = substr($validatedValue, -strlen($value)) == $value;
                }
                break;
            default:
                return parent::validateAttribute($validatedValue);
                break;
        }
        return $result;
    }
}
