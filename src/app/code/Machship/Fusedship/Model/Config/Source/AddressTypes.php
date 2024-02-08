<?php

namespace Machship\Fusedship\Model\Config\Source;

class AddressTypes implements \Magento\Framework\Data\OptionSourceInterface {

    /**
     *
     * @return array
     */
	public function toOptionArray()
	{
		return [
			['value' => '1', 'label' => __('Residential')],
			['value' => '2', 'label' => __('Business')],
		];
	}
}
