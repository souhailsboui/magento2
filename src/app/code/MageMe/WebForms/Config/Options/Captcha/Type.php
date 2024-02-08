<?php

namespace MageMe\WebForms\Config\Options\Captcha;

use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface
{
    const RECAPTCHA = 'recaptcha';
    const HCAPTCHA = 'hcaptcha';
    const TURNSTILE = 'turnstile';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('reCAPTCHA'),
                'value' => self::RECAPTCHA
            ],
            [
                'label' => __('hCaptcha'),
                'value' => self::HCAPTCHA
            ],
            [
                'label' => __('Turnstile'),
                'value' => self::TURNSTILE
            ],
        ];
    }
}