<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Plugin\Catalog\Model;

use Amasty\VisualMerchCore\Model\Indexer\Catalog\Product\Eav\Processor;
use Magento\Catalog\Model\Product;

class ProductPlugin
{
    private $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param Product $subject
     */
    public function beforeEavReindexCallback(Product $subject): void
    {
        if ($subject->isObjectNew() || $subject->isDataChanged()) {
            $this->processor->reindexRow($subject->getEntityId());
        }
    }
}
