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
 * @package     Mageplaza_StoreCredit
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\StoreCredit\Model\Config\Source;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class FieldRenderer
 * @package Mageplaza\StoreCredit\Model\Config\Source
 */
class FieldRenderer extends AbstractModel implements ArrayInterface
{
    const CREDIT_AMOUNT = 'credit_amount';
    const CREDIT_RANGE = 'credit_range';
    const CREDIT_RATE = 'credit_rate';

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::CREDIT_AMOUNT => __('Store Credit'),
            self::CREDIT_RANGE => __('Allow Credit Range'),
            self::CREDIT_RATE => __('Price Percentage'),
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
