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

namespace Mageplaza\StoreCredit\Model\Quote\Item;

use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;

/**
 * Class CartItemProcessor
 * @package Mageplaza\StoreCredit\Model\Quote\Item
 */
class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var Factory
     */
    protected $objectFactory;

    /**
     * CartItemProcessor constructor.
     *
     * @param Factory $objectFactory
     */
    public function __construct(
        Factory $objectFactory
    ) {
        $this->objectFactory = $objectFactory;
    }

    /**
     * @inheritdoc
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption() &&
            $cartItem->getProductOption()->getExtensionAttributes() &&
            $cartItem->getProductOption()->getExtensionAttributes()->getCreditAmount()
        ) {
            return $this->objectFactory->create(
                [
                    'credit_amount' => $cartItem->getProductOption()
                        ->getExtensionAttributes()->getCreditAmount()
                ]
            );
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function processOptions(CartItemInterface $cartItem)
    {
        return $cartItem;
    }
}
