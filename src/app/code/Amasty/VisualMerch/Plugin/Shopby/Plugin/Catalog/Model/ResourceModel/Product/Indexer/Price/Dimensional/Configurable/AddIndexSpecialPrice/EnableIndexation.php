<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

// phpcs:disable Generic.Files.LineLength
namespace Amasty\VisualMerch\Plugin\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price\Dimensional\Configurable\AddIndexSpecialPrice;

class EnableIndexation
{
    /**
     * If Amasty_VisualMerch enabled , need always index special price for configurables.
     */
    public function afterIsActive(): bool
    {
        return true;
    }
}
