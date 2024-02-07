<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Store;

use Magento\Store\Model\StoreManagerInterface;

class GetStores
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ['root_category_id' => [store_id, ...], ...]
     * @var array
     */
    private $storesByRootCategory;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @return array ['root_category_id' => [store_id, ...], ...]
     */
    public function execute(): array
    {
        if ($this->storesByRootCategory === null) {
            $this->storesByRootCategory = [];
            foreach ($this->storeManager->getGroups() as $group) {
                if (!isset($this->storesByRootCategory[$group->getRootCategoryId()])) {
                    $this->storesByRootCategory[$group->getRootCategoryId()] = [];
                }
                array_push(
                    $this->storesByRootCategory[$group->getRootCategoryId()],
                    ...array_map(function ($storeId) {
                        return (int)$storeId;
                    }, $group->getStoreIds())
                );
            }
        }

        return $this->storesByRootCategory;
    }
}
