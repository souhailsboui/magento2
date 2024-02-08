<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Cron;

class CatalogCategoryProductReindex
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product
     */
    private $indexer;

    public function __construct(
        \Magento\Catalog\Model\Indexer\Category\Product $indexer
    ) {
        $this->indexer = $indexer;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->indexer->executeFull();
    }
}
