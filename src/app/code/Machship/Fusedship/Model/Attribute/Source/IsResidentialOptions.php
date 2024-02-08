<?php

namespace Machship\Fusedship\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class IsResidentialOptions extends AbstractSource
{
    /**
     * Get options for the custom attribute
     *
     * @return array
     */
    public function getAllOptions()
    {

        return [
            ['label' => 'Address Type: Residential', 'value' => '1'],
            ['label' => 'Address Type: Business', 'value' => '2']
        ];
    }
}