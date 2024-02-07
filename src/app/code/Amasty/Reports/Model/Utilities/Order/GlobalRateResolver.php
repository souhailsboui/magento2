<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\Utilities\Order;

use Amasty\Reports\Model\Store as StoreResolver;
use Magento\Store\Model\Store;

class GlobalRateResolver
{
    /**
     * @var StoreResolver
     */
    private $storeResolver;

    public function __construct(StoreResolver $storeResolver)
    {
        $this->storeResolver = $storeResolver;
    }

    public function isDefaultStore(): bool
    {
        return $this->storeResolver->getCurrentStoreId() == Store::DEFAULT_STORE_ID;
    }

    public function resolvePriceColumn(string $columnName): string
    {
        if ($this->isDefaultStore()) {
            $columnName .= ' * base_to_global_rate';
        }

        return $columnName;
    }
}
