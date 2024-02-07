<?php
namespace Biztech\Ausposteparcel\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Unit implements ArrayInterface
{
    public function toOptionArray()
    {
        $units = ['kg' => "KG", 'gm' => "GM"];
        foreach ($units as $key => $unit) {
            $allProductUnits[] = ['label' => $unit, 'value' => $key];
        }

        return $allProductUnits;
    }
}
