<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\ResourceModel\Product\Collection;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Model\StoreManagerInterface;

class AddVisibilityFilter
{
    private const VISIBLE_PRODUCT_TALBE = 'amasty_merchandiser_visible_product';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Method implements visibility filter for Merchandiser Product Collection
     *
     * @param Collection $collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Collection $collection): void
    {
        $table = $collection->getResource()->getTable(self::VISIBLE_PRODUCT_TALBE);
        $collection->getSelect()->joinInner(
            ['visible_product' => $table],
            sprintf("visible_product.product_id = e.%s", $collection->getEntity()->getIdFieldName()),
            []
        );
        $storeId = $collection->getStoreId() ?: $this->storeManager->getStore()->getId();
        $collection->getSelect()->where('visible_product.store_id = ?', $storeId);
    }
}
