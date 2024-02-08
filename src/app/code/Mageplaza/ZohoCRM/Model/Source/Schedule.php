<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Schedule
 * @package Mageplaza\ZohoCRM\Model\Source
 */
class Schedule implements OptionSourceInterface
{
    const DAILY   = 'daily';
    const WEEKLY  = 'weekly';
    const MONTHLY = 'monthly';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '120', 'label' => __('2 Minutes')],
            ['value' => '300', 'label' => __('5 Minutes')],
            ['value' => '900', 'label' => __('15 Minutes')],
            ['value' => '1800', 'label' => __('30 Minutes')],
            ['value' => '3600', 'label' => __('1 hour')],
            ['value' => self::DAILY, 'label' => __('Daily')],
            ['value' => self::WEEKLY, 'label' => __('Weekly')],
            ['value' => self::MONTHLY, 'label' => __('Monthly')],
        ];
    }
}
