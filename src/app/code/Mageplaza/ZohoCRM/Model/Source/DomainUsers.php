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
 * Class DomainUsers
 * @package Mageplaza\ZohoCRM\Model\Source
 */
class DomainUsers implements OptionSourceInterface
{
    const US = 'US';
    const EU = 'EU';
    const CN = 'CN';
    const IN = 'IN';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::US, 'label' => __('US')],
            ['value' => self::EU, 'label' => __('EU')],
            ['value' => self::CN, 'label' => __('CN')],
            ['value' => self::IN, 'label' => __('IN')],
        ];
    }
}
