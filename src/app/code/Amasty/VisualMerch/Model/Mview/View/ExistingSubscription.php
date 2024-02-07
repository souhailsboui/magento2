<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Mview\View;

use Magento\Framework\Mview\View\Subscription;

class ExistingSubscription extends Subscription
{
    /**
     * @return ExistingSubscription
     */
    public function create(bool $save = true)
    {
        if ($this->isSubscriptionTableExist()) {
            parent::create();
        }
        return $this;
    }

    /**
     * @return ExistingSubscription
     */
    public function remove()
    {
        if ($this->isSubscriptionTableExist()) {
            parent::remove();
        }
        return $this;
    }

    public function isSubscriptionTableExist(): bool
    {
        return $this->resource->getConnection()->isTableExists($this->resource->getTableName($this->tableName));
    }
}
