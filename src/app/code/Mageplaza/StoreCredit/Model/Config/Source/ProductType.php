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

use Magento\Catalog\Model\Product\Type;
use Mageplaza\StoreCredit\Model\Product\Type\StoreCredit;

/**
 * Class ProductType
 *
 * @package Mageplaza\StoreCredit\Model\Config\Source
 */
class ProductType extends Type
{
    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        $options = parent::getOptionArray();

        unset($options[StoreCredit::TYPE_STORE_CREDIT]);

        return $options;
    }
}
