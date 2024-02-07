<?php

namespace Biztech\Ausposteparcel\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class States implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'ACT', 'label' => 'ACT'],
            ['value' => 'NSW', 'label' => 'NSW'],
            ['value' => 'QLD', 'label' => 'QLD'],
            ['value' => 'SA', 'label' => 'SA'],
            ['value' => 'TAS', 'label' => 'TAS'],
            ['value' => 'VIC', 'label' => 'VIC'],
            ['value' => 'WA', 'label' => 'WA'],
            ['value' => 'NT', 'label' => 'NT'],
        ];
        return $options;
    }
}
