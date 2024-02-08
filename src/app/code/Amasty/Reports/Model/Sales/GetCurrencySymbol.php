<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\Sales;

use Amasty\Reports\Model\Store as StoreResolver;
use Magento\Store\Model\StoreManagerInterface;

class GetCurrencySymbol
{
    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreResolver $storeResolver, StoreManagerInterface $storeManager)
    {
        $this->storeResolver = $storeResolver;
        $this->storeManager = $storeManager;
    }

    public function execute(): string
    {
        return (string)$this->storeManager
            ->getStore($this->storeResolver->getCurrentStoreId())
            ->getBaseCurrency()
            ->getCurrencySymbol();
    }
}
