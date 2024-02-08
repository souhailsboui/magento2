<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Traits;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

trait RuleTrait
{
    public function addReportRules(AbstractCollection $collection): void
    {
        $filters = $this->getRequestParams();
        if (isset($filters['rule']) && $filters['rule']) {
            $storeId = isset($filters['store']) ? (int)$filters['store'] : 0;
            $productIds = $this->ruleIndex->getAppliedProducts(
                (int)$filters['rule'],
                $storeId
            );
            $collection->getSelect()->where('sales_order_item.product_id in (?)', $productIds);
        }
    }
}
