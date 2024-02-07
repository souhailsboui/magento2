<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\Mview\View;

use Magento\Framework\Mview\View\Subscription as MviewSubscription;

class Subscription extends MviewSubscription
{
    public function create(bool $save = true)
    {
        if ($this->isSubscriptionTableExist()) {
            parent::create($save);
        }

        return $this;
    }

    public function remove(): Subscription
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
