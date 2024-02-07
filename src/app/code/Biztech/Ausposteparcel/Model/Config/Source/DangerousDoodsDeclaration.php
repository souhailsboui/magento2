<?php

namespace Biztech\Ausposteparcel\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class DangerousDoodsDeclaration implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'UN1845_DryIce_and_UN3373_BioSubstance_B', 'label' => 'UN1845_DryIce_and_UN3373_BioSubstance_B'],
            ['value' => 'UN2910_radioactive_excepted_limited_qty', 'label' => 'UN2910_radioactive_excepted_limited_qty'],
            ['value' => 'UN2911_radioactive_excepted_instruments_or_articles', 'label' => 'UN2911_radioactive_excepted_instruments_or_articles'],
            ['value' => 'UN3091_Lithium_MetalAndAlloy_contained_in_equipment', 'label' => 'UN3091_Lithium_MetalAndAlloy_contained_in_equipment'],
            ['value' => 'UN3373_BioSubstance_B', 'label' => 'UN3373_BioSubstance_B'],
            ['value' => 'UN3481_Lithium_IonOrPolymer_contained_in_equipment', 'label' => 'UN3481_Lithium_IonOrPolymer_contained_in_equipment'],
        ];
        return $options;
    }
}
