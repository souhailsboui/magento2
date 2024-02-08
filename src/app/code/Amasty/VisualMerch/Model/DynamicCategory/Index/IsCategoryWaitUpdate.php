<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Index;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\CategoryProcessor;
use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\Index\LoadCategoryVersion;

/**
 * Check is amasty_merch_dynamic_category_product in schedule mode
 * and passed category wait update via cron.
 */
class IsCategoryWaitUpdate
{
    /**
     * @var CategoryProcessor
     */
    private $categoryProcessor;

    /**
     * @var LoadCategoryVersion
     */
    private $loadCategoryVersion;

    public function __construct(CategoryProcessor $categoryProcessor, LoadCategoryVersion $loadCategoryVersion)
    {
        $this->categoryProcessor = $categoryProcessor;
        $this->loadCategoryVersion = $loadCategoryVersion;
    }

    public function execute(int $categoryId): bool
    {
        if (!$this->categoryProcessor->isIndexerScheduled()) {
            return false;
        }

        return $this->categoryProcessor->getIndexer()->getView()->getState()->getVersionId()
            < $this->loadCategoryVersion->execute($categoryId);
    }
}
