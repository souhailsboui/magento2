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

namespace Mageplaza\StoreCredit\Helper;

use Magento\Quote\Model\Quote\Item;
use Mageplaza\StoreCredit\Model\Config\Source\FieldRenderer;

/**
 * Class Product
 * @package Mageplaza\StoreCredit\Helper
 */
class Product extends Data
{
    const USE_CONFIG = 'use_config';

    /**
     * @param $item
     * @param array $options
     *
     * @return array
     */
    public function getOptionList($item, $options = [])
    {
        $fields = FieldRenderer::getOptionArray();
        $value = $this->getOptionValue(FieldRenderer::CREDIT_AMOUNT, $item);

        if ($value) {
            $optionList = [
                [
                    'label' => $fields[FieldRenderer::CREDIT_AMOUNT],
                    'value' => $this->convertPrice($value, true, false),
                    'custom_view' => true
                ]
            ];

            return array_merge($optionList, $options);
        }

        return $options;
    }

    /**
     * @param $optionCode
     * @param Item|\Magento\Sales\Model\Order\Item $item
     *
     * @return mixed
     */
    protected function getOptionValue($optionCode, $item)
    {
        if ($item instanceof Item && $option = $item->getOptionByCode($optionCode)) {
            return $option->getValue();
        } elseif ($option = $item->getProductOptionByCode($optionCode)) {
            return $option;
        }

        return false;
    }
}
