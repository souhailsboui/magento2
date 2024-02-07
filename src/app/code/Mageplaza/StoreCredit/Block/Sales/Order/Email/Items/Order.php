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

namespace Mageplaza\StoreCredit\Block\Sales\Order\Email\Items;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder;
use Mageplaza\StoreCredit\Helper\Product;

/**
 * Class Order
 * @package Mageplaza\StoreCredit\Block\Order\Email\Items
 */
class Order extends DefaultOrder
{
    /**
     * @return array
     */
    public function getItemOptions()
    {
        /** @var Product $helper */
        $helper = ObjectManager::getInstance()->get(Product::class);
        $item = $this->getItem();

        return $helper->getOptionList($item, parent::getItemOptions());
    }
}
