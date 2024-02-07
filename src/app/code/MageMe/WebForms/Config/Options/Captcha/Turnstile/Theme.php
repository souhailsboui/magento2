<?php

namespace MageMe\WebForms\Config\Options\Captcha\Turnstile;

use Magento\Framework\Data\OptionSourceInterface;

class Theme implements OptionSourceInterface
{
    const AUTO = 'auto';
    const LIGHT = 'light';
    const DARK = 'dark';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::AUTO, 'label' => __('Auto')],
            ['value' => self::LIGHT, 'label' => __('Light')],
            ['value' => self::DARK, 'label' => __('Dark')],
        ];
    }
}