<?php

namespace Meetanshi\ShippingRules\Plugin\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Config;
use Meetanshi\ShippingRules\Model\ResourceModel\Rule;

class Attributes
{
    protected $rule;

    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
    }

    public function aroundGetProductAttributes(Config $subject, \Closure $closure)
    {
        $attributeTran = $closure();

        $attributes = $this->rule->getAttributes();
        foreach ($attributes as $attr) {
            $attributeTran[] = $attr;
        }

        return $attributeTran;
    }
}
