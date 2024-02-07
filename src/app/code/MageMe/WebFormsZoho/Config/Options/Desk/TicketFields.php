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

class TicketFields implements OptionSourceInterface
{
    const CUSTOM = 'custom';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = $this->defaultOptions();
        $options[] = [
            'label' => __('Custom'),
            'value' => self::CUSTOM
        ];
        return $options;
    }

    /**
     * @return array
     */
    private function defaultOptions(): array
    {
        return [
            [
                'label' => __('Subject'),
                'value' => 'subject'
            ],
            [
                'label' => __('File Attachments'),
                'value' => 'uploads'
            ],
            [
                'label' => __('Email'),
                'value' => 'email'
            ],
            [
                'label' => __('Phone Number'),
                'value' => 'phone'
            ],
            [
                'label' => __('Description'),
                'value' => 'description'
            ],
            [
                'label' => __('Category'),
                'value' => 'category'
            ],
            [
                'label' => __('Subcategory'),
                'value' => 'subCategory'
            ],
            [
                'label' => __('Due Date'),
                'value' => 'dueDate'
            ],
            [
                'label' => __('Priority'),
                'value' => 'priority'
            ],
            [
                'label' => __('Language'),
                'value' => 'language'
            ],
            [
                'label' => __('Channel'),
                'value' => 'channel'
            ],
            [
                'label' => __('Type Of Ticket'),
                'value' => 'classification'
            ]
        ];
    }
}