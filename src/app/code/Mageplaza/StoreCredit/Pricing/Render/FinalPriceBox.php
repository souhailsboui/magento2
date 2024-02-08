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

namespace Mageplaza\StoreCredit\Pricing\Render;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Render\FinalPriceBox as CatalogRender;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class for final_price rendering
 */
class FinalPriceBox extends CatalogRender
{
    /**
     * @var AmountFactory
     */
    protected $amountFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Min max values
     *
     * @var array
     */
    protected $_minMax = [];

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->findMinMaxValue();
    }

    /**
     * FinalPriceBox constructor.
     *
     * @param AmountFactory $amountFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param array $data
     * @param SalableResolverInterface|null $salableResolver
     * @param MinimalPriceCalculatorInterface|null $minimalPriceCalculator
     */
    public function __construct(
        AmountFactory $amountFactory,
        PriceCurrencyInterface $priceCurrency,
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        array $data = [],
        SalableResolverInterface
        $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        $this->amountFactory = $amountFactory;
        $this->priceCurrency = $priceCurrency;

        parent::__construct($context, $saleableItem, $price, $rendererPool, $data, $salableResolver, $minimalPriceCalculator);
    }

    /**
     * @return AmountFactory
     */
    public function getAmountFactory()
    {
        return $this->amountFactory;
    }

    /**
     * @return PriceCurrencyInterface
     */
    protected function getPriceCurrency()
    {
        return $this->priceCurrency;
    }

    /**
     * @return AmountInterface
     */
    public function getMinPrice()
    {
        $minPrice = $this->getPriceCurrency()->convert($this->_minMax['min']);

        return $this->getAmountFactory()->create($minPrice);
    }

    /**
     * @return AmountInterface
     */
    public function getMaxPrice()
    {
        $maxPrice = $this->getPriceCurrency()->convert($this->_minMax['max']);

        return $this->getAmountFactory()->create($maxPrice);
    }

    /**
     * @return bool
     */
    public function isFixedPrice()
    {
        return !$this->saleableItem->getAllowCreditRange() && $this->saleableItem->getCreditAmount();
    }

    /**
     * @return $this
     */
    protected function findMinMaxValue()
    {
        $rate = $this->saleableItem->getCreditRate() ? $this->saleableItem->getCreditRate() / 100 : 1;

        $min = $max = $this->saleableItem->getCreditAmount() * $rate;

        if ($this->saleableItem->getAllowCreditRange()) {
            $min = $this->saleableItem->getMinCredit() * $rate;
            $max = $this->saleableItem->getMaxCredit() * $rate;
        }

        $this->_minMax = [
            'min' => $min,
            'max' => $max
        ];

        return $this;
    }
}
