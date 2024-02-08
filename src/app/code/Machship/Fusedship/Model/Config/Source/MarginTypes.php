<?php

namespace Machship\Fusedship\Model\Config\Source;

class MarginTypes implements \Magento\Framework\Data\OptionSourceInterface {

    /**
     *
     * @return array
     */
	public function toOptionArray()
	{
		return [
			['value' => '$', 'label' => __('Fixed Amount')],
			['value' => '%', 'label' => __('Percentage')],
		];
	}
}
