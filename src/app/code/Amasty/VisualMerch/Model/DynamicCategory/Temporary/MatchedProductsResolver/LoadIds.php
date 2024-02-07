<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver;

use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\CategoryTemporary;

class LoadIds
{
    /**
     * @var CategoryTemporary
     */
    private $categoryTemporary;

    public function __construct(CategoryTemporary $categoryTemporary)
    {
        $this->categoryTemporary = $categoryTemporary;
    }

    public function execute(string $hash, ?int $categoryId = null): ?array
    {
        if ($categoryId) {
            $matchedProductIds = $this->categoryTemporary->loadByHashAndCategoryId($hash, $categoryId);
        } else {
            $matchedProductIds = $this->categoryTemporary->loadByHash($hash);
        }

        if ($matchedProductIds !== null) {
            $matchedProductIds = array_filter(explode(',', $matchedProductIds));
        }

        return $matchedProductIds;
    }
}
