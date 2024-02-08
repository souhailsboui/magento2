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

namespace Mageplaza\StoreCredit\Plugin\Quote;

use Closure;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Model\Order\Item;
use Mageplaza\StoreCredit\Helper\Data;
use Mageplaza\StoreCredit\Model\Config\Source\FieldRenderer;

/**
 * Class ToOrderItem
 * @package Mageplaza\StoreCredit\Plugin\Quote
 */
class ToOrderItem
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * ToOrderItem constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param Closure $proceed
     * @param AbstractItem $item
     * @param $additional
     *
     * @return Item
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        Closure $proceed,
        AbstractItem $item,
        $additional = []
    ) {
        /** @var Item $orderItem */
        $orderItem = $proceed($item, $additional);

        $productOptions = $orderItem->getProductOptions();

        foreach (FieldRenderer::getOptionArray() as $key => $label) {
            if ($option = $item->getProduct()->getCustomOption($key)) {
                $productOptions[$key] = $option->getValue();
            }
        }

        $orderItem->setProductOptions($productOptions);

        return $orderItem;
    }
}
