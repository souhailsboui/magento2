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

namespace MageMe\WebFormsZoho\Config\Options;

use Magento\Framework\Data\OptionSourceInterface;

class ZohoDomain implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array {
        return [
            [
                'label' => 'US: https://accounts.zoho.com',
                'value' => 'https://accounts.zoho.com'
            ],
            [
                'label' => 'AU: https://accounts.zoho.com.au',
                'value' => 'https://accounts.zoho.com.au'
            ],
            [
                'label' => 'EU: https://accounts.zoho.eu',
                'value' => 'https://accounts.zoho.eu'
            ],
            [
                'label' => 'IN: https://accounts.zoho.in',
                'value' => 'https://accounts.zoho.in'
            ],
            [
                'label' => 'CN: https://accounts.zoho.com.cn',
                'value' => 'https://accounts.zoho.com.cn'
            ],
            [
                'label' => 'JP: https://accounts.zoho.jp',
                'value' => 'https://accounts.zoho.jp'
            ],
        ];
    }
}