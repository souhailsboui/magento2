<?php

namespace Biztech\Ausposteparcel\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Biztech\Ausposteparcel\Helper\Data;

class Enabledisable implements ArrayInterface
{
    protected $helper;

    /**
     * Enabledisable constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helper = $helperData;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 0, 'label' => __('No')],
        ];

        $websites = $this->helper->getAllWebsites();
        if (!empty($websites)) {
            $options[] = ['value' => 1, 'label' => __('Yes')];
        }
        return $options;
    }
}
