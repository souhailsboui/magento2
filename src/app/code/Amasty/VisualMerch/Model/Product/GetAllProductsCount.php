<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Product;

use Amasty\VisualMerch\Model\ResourceModel\Product\LoadAllProductsCount;

class GetAllProductsCount
{
    /**
     * @var LoadAllProductsCount
     */
    private $loadAllProductsCount;

    /**
     * @var int|null
     */
    private $count;

    public function __construct(LoadAllProductsCount $loadAllProductsCount)
    {
        $this->loadAllProductsCount = $loadAllProductsCount;
    }

    public function execute(): int
    {
        if ($this->count === null) {
            $this->count = $this->loadAllProductsCount->execute();
        }
        return $this->count;
    }
}
