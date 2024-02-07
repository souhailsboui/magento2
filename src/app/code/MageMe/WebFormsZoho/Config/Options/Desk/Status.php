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

namespace MageMe\WebFormsZoho\Config\Options\Desk;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->defaultOptions();
    }

    /**
     * @return array
     */
    private function defaultOptions(): array
    {
        return [
            [
                'label' => __('Open'),
                'value' => 'Open'
            ],
            [
                'label' => __('On Hold'),
                'value' => 'On Hold'
            ],
            [
                'label' => __('Escalated'),
                'value' => 'Escalated'
            ],
            [
                'label' => __('Closed'),
                'value' => 'Closed'
            ],
        ];
    }
}