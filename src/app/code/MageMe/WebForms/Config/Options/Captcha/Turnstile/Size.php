<?php

namespace MageMe\WebForms\Config\Options\Captcha\Turnstile;

use Magento\Framework\Data\OptionSourceInterface;

class Size implements OptionSourceInterface
{
    const NORMAL = 'normal';
    const COMPACT = 'compact';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::NORMAL, 'label' => __('Normal')],
            ['value' => self::COMPACT, 'label' => __('Compact')],
        ];
    }
}