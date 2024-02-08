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

namespace Mageplaza\StoreCredit\Block\Checkout\Item;

use Magento\Framework\App\ObjectManager;
use Mageplaza\StoreCredit\Helper\Product;

/**
 * Class Renderer
 * @package Mageplaza\StoreCredit\Block\Checkout\Item
 */
class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    /**
     * Return store credit custom options
     *
     * @return array
     */
    public function getOptionList()
    {
        /** @var Product $helper */
        $helper = ObjectManager::getInstance()->get(Product::class);
        $item = $this->getItem();
        $customOptions = $this->_productConfig->getCustomOptions($item);

        return $helper->getOptionList($item, $customOptions);
    }
}
