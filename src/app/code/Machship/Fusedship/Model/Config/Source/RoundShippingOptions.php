<?php

namespace Machship\Fusedship\Model\Config\Source;

class RoundShippingOptions implements \Magento\Framework\Data\OptionSourceInterface {

    /**
     *
     * @return array
     */
	public function toOptionArray()
	{
		return [
			['value' => 'DoNotRound', 'label' => __('Do Not Round')],
			['value' => 'RoundUpNearestDollar', 'label' => __('Up to Nearest Dollar')],
			['value' => 'RoundDownNearestDollar', 'label' => __('Down Nearest Dollar')],
			['value' => 'RoundUpOrDown', 'label' => __('Up or Down')],
			['value' => 'RoundCeil', 'label' => __('Round up always')],
		];
	}
}
