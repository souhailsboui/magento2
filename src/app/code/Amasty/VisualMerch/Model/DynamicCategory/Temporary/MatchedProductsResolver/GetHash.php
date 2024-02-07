<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver;

class GetHash
{
    /**
     * @param int[] $storeIds
     * @param string $conditionsSerialized
     */
    public function execute(array $storeIds, string $conditionsSerialized): string
    {
        sort($storeIds, SORT_NUMERIC);
        return hash(
            'sha256',
            sprintf('%s_store_%s', $conditionsSerialized, implode('-', $storeIds))
        );
    }
}
