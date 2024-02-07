<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Category;

use Magento\Catalog\Api\Data\CategoryInterface;

class ResolveRootCategoryId
{
    public function execute(CategoryInterface $category): int
    {
        if ($category->hasLevel() && $category->getLevel() == 1) {
            return (int)$category->getId();
        }
        return (int)($category->getParentIds()[1] ?? null); // 1 - is root category id key in array
    }
}
