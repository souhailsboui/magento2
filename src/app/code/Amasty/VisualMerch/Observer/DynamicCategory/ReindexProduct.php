<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Observer\DynamicCategory;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\ProductProcessor;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReindexProduct implements ObserverInterface
{
    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    public function __construct(ProductProcessor $productProcessor)
    {
        $this->productProcessor = $productProcessor;
    }

    public function execute(Observer $observer)
    {
        /** @var Product $product */
        if ($product = $observer->getEvent()->getProduct()) {
            $this->productProcessor->reindexRow($product->getId());
        }
    }
}
