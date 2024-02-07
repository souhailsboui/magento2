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

namespace Mageplaza\StoreCredit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price as CatalogPrice;
use Magento\Quote\Model\Quote\Item\Option;
use Mageplaza\StoreCredit\Model\Config\Source\FieldRenderer;

/**
 * Class Price
 * @package Mageplaza\StoreCredit\Model\Product
 */
class Price extends CatalogPrice
{
    /**
     * @param Product $product
     *
     * @return float
     */
    public function getPrice($product)
    {
        $creditRate = $product->getCreditRate() ? $product->getCreditRate() / 100 : 1;
        $price = $product->getCreditAmount() * $creditRate;

        if ($product->hasCustomOptions()) {
            /** @var Option $amount */
            $amount = $product->getCustomOption(FieldRenderer::CREDIT_AMOUNT);
            /** @var Option $rate */
            $rate = $product->getCustomOption(FieldRenderer::CREDIT_RATE);

            $creditRate = $rate->getValue() ? $rate->getValue() / 100 : 1;
            $price = ($amount && $rate) ? ($amount->getValue() * $creditRate) : 0;
        } elseif ($product->getAllowCreditRange()) {
            $price = $product->getMinCredit() * $creditRate;
        }

        return $price;
    }

    /**
     * @param float|null $qty
     * @param Product $product
     *
     * @return float
     */
    public function getFinalPrice($qty, $product)
    {
        if (is_null($qty) && $product->getCalculatedFinalPrice() !== null) {
            return $product->getCalculatedFinalPrice();
        }

        $creditRate = $product->getCreditRate() ? $product->getCreditRate() / 100 : 1;
        $finalPrice = $product->getCreditAmount() * $creditRate;

        if ($product->hasCustomOptions()) {
            /** @var Option $amount */
            $amount = $product->getCustomOption(FieldRenderer::CREDIT_AMOUNT);
            /** @var Option $rate */
            $rate = $product->getCustomOption(FieldRenderer::CREDIT_RATE);

            $creditRate = $rate->getValue() ? $rate->getValue() / 100 : 1;
            $finalPrice = ($amount && $rate) ? ($amount->getValue() * $creditRate) : 0;
        } elseif ($product->getAllowCreditRange()) {
            $finalPrice = $product->getMinCredit() * $creditRate;
        }

        $product->setFinalPrice($finalPrice);

        $this->_eventManager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $qty]);

        $finalPrice = $product->getData('final_price');
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice = max(0, $finalPrice);

        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }
}
