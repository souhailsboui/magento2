<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Xlanding\Model\Indexer\Catalog\Category;

use Amasty\Xlanding\Model\Indexer\Catalog\Category\Product as XlandingProductIndexer;

/**
 * Plugin for resolving indexing conflicts between Amasty_Xlanding and Amasty_VisualMerch.
 * Indexing was suppressed because if Amasty_VisualMerch is enabled, it should not be
 *
 * Class Product
 * @package Amasty\VisualMerch\Plugin\Xlanding\Model\Indexer\Catalog\Category
 */
class Product
{
    /**
     * @param XlandingProductIndexer $subject
     * @param array $categoryIds
     * @return XlandingProductIndexer
     */
    public function aroundExecuteCategories(
        XlandingProductIndexer $subject,
        $categoryIds = []
    ) {
        return $subject;
    }

    /**
     * @param XlandingProductIndexer $subject
     * @param array $productIds
     * @return XlandingProductIndexer
     */
    public function aroundExecuteProducts(
        XlandingProductIndexer $subject,
        $productIds = []
    ) {
        return $subject;
    }

    /**
     * @param XlandingProductIndexer $subject
     * @return XlandingProductIndexer
     */
    public function aroundExecuteFull(XlandingProductIndexer $subject)
    {
        return $subject;
    }
}
