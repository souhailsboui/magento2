<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Catalog\Model\ResourceModel\Product\CategoryLink;

use Amasty\VisualMerch\Model\CategoryLink\GetAvailablePosition;
use Amasty\VisualMerch\Model\ResourceModel\CategoryLink\SaveLinks;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink as CategoryProductLink;

class RankCategoryProductPosition
{
    /**
     * @var GetAvailablePosition
     */
    private $getAvailablePosition;

    /**
     * @var SaveLinks
     */
    private $saveLinks;

    public function __construct(GetAvailablePosition $getAvailablePosition, SaveLinks $saveLinks)
    {
        $this->getAvailablePosition = $getAvailablePosition;
        $this->saveLinks = $saveLinks;
    }

    /**
     * Rank category product positions.
     *
     * @param CategoryProductLink $categoryProductLink
     * @param callable $proceed
     * @param ProductInterface $product
     * @param array $insertLinks
     * @param bool $insert
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundUpdateCategoryLinks(
        CategoryProductLink $categoryProductLink,
        callable $proceed,
        ProductInterface $product,
        array $insertLinks,
        $insert = false
    ): array {
        foreach ($insertLinks as &$link) {
            $link['position'] = $this->getAvailablePosition->execute((int) $link['category_id']);
            $link['product_id'] = (int) $product->getId();
        }
        if ($insertLinks) {
            $this->saveLinks->execute($insertLinks);
        }

        return array_column($insertLinks, 'category_id');
    }
}
