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

namespace Mageplaza\StoreCredit\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Quote\Api\CartRepositoryInterface;
use Mageplaza\StoreCredit\Helper\Data;

/**
 * Class View
 * @package Mageplaza\StoreCredit\Block\Product
 */
class View extends AbstractView
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LocaleFormat
     */
    protected $localeFormat;

    /**
     * View constructor.
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param CartRepositoryInterface $quoteRepository
     * @param Data $helper
     * @param LocaleFormat $localeFormat
     * @param array $data
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        CartRepositoryInterface $quoteRepository,
        Data $helper,
        LocaleFormat $localeFormat,
        array $data = []
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->helper          = $helper;
        $this->localeFormat    = $localeFormat;

        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     * @return array
     */
    public function getInformation()
    {
        $product = $this->getProduct();

        $information = [
            'productId'    => $product->getId(),
            'creditAmount' => $this->convertPrice($product->getCreditAmount()),
            'creditRate'   => $product->getCreditRate() ? $product->getCreditRate() / 100 : 1,
            'creditRange'  => !!$product->getAllowCreditRange(),
            'minCredit'    => $this->convertPrice($product->getMinCredit()),
            'maxCredit'    => $this->convertPrice($product->getMaxCredit()),
            'rangerUpdate' => $this->getItemRangerUpdate(),
            'currencyRate' => $this->convertPrice(1),
            'priceFormat'  => $this->localeFormat->getPriceFormat()
        ];

        return $information;
    }

    /**
     * @param $amount
     *
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->helper->formatPrice($amount, false);
    }

    /**
     * @param $amount
     *
     * @return string
     */
    public function convertPrice($amount)
    {
        return $this->helper->convertPrice($amount, false);
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemRangerUpdate()
    {
        $rangerUpdate = 0;

        $productId = $this->getRequest()->getParam('product_id');
        $itemId    = $this->getRequest()->getParam('id');

        if ($quoteId = $this->helper->getCheckoutSession()->getQuoteId()) {
            $quote = $this->quoteRepository->get($quoteId);
            foreach ($quote->getAllItems() as $item) {
                if ($productId === $item->getProductId() && $itemId === $item->getItemId()) {
                    $rangerUpdate = $item->getBasePrice();
                }
            }
        }

        return $rangerUpdate;
    }
}
