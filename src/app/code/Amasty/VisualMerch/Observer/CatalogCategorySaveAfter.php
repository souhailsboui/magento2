<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Observer;

use Magento\Catalog\Model\Indexer\Category\Product\Processor as CategoryProductProcessor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogCategorySaveAfter implements ObserverInterface
{
    /**
     * @var CategoryProductProcessor
     */
    private $categoryProductProcessor;

    public function __construct(CategoryProductProcessor $categoryProductProcessor)
    {
        $this->categoryProductProcessor = $categoryProductProcessor;
    }

    public function execute(Observer $observer)
    {
        $categoryId = $observer->getCategory()->getEntityId();

        if ($categoryId) {
            $this->categoryProductProcessor->reindexRow($categoryId);
        }
    }
}
