<?php

namespace Machship\Fusedship\Model;

use Magento\Customer\Model\Address as MagentoAddress;

class Address extends MagentoAddress
{
    public function getIsResidential()
    {
        return $this->_getData('is_residential');
    }

    public function setIsResidential($value)
    {
        return $this->setData('is_residential', $value);
    }
}