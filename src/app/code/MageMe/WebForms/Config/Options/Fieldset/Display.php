<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Config\Options\Fieldset;

use Magento\Framework\Data\OptionSourceInterface;

class Display implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->options) {
            $this->options = [
                ['value' => 'on', 'label' => __('On')],
                ['value' => 'off', 'label' => __('Off')],
            ];
        }

        return $this->options;
    }
}
