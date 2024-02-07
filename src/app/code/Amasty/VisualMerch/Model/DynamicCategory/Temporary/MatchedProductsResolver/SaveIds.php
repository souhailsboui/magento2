<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver;

use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\CategoryTemporary;

class SaveIds
{
    /**
     * @var CategoryTemporary
     */
    private $categoryTemporary;

    public function __construct(CategoryTemporary $categoryTemporary)
    {
        $this->categoryTemporary = $categoryTemporary;
    }

    public function execute(string $hash, int $categoryId, array $matchedProductIds): void
    {
        $this->categoryTemporary->insert([
            CategoryTemporary::HASH_COLUMN => $hash,
            CategoryTemporary::CATEGORY_ID_COLUMN => $categoryId,
            CategoryTemporary::MATCHED_IDS_COLUMN => implode(',', $matchedProductIds)
        ]);
    }
}
