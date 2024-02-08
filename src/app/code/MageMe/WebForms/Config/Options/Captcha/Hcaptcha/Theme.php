<?php

namespace MageMe\WebForms\Config\Options\Captcha\Hcaptcha;

use Magento\Framework\Data\OptionSourceInterface;

class Theme implements OptionSourceInterface
{
    const LIGHT = 'light';
    const DARK = 'dark';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::LIGHT, 'label' => __('Light')],
            ['value' => self::DARK, 'label' => __('Dark')],
        ];
    }
}