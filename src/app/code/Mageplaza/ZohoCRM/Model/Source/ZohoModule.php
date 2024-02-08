<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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
 * Class ZohoModule
 * @package Mageplaza\ZohoCRM\Model\Source
 */
class ZohoModule implements OptionSourceInterface
{
    const ACCOUNT  = 'account';
    const PRODUCT  = 'product';
    const ORDER    = 'order';
    const INVOICE  = 'invoice';
    const CAMPAIGN = 'campaign';
    const LEAD     = 'lead';
    const CONTACT  = 'contact';

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::ACCOUNT  => __('Account'),
            self::PRODUCT  => __('Product'),
            self::ORDER    => __('Purchase Order'),
            self::INVOICE  => __('Invoice'),
            self::CAMPAIGN => __('Campaign'),
            self::LEAD     => __('Lead'),
            self::CONTACT  => __('Contact'),
        ];
    }

    /**
     * @return array
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