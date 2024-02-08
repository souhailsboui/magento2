<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\Sales;

use Amasty\Reports\Model\Store as StoreResolver;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class FormatPrice
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        StoreResolver $storeResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->storeResolver = $storeResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Format price to current BASE Currency.
     * Don't convert to DISPLAY currency (because lost order rate).
     */
    public function execute(float $price): string
    {
        return $this->priceCurrency->format(
            $price,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $this->storeManager->getStore($this->storeResolver->getCurrentStoreId())->getBaseCurrency()
        );
    }
}
