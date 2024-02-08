<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Notification;

use Amasty\Reports\Api\Data\NotificationInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_setIdFieldName(NotificationInterface::ENTITY_ID);
        $this->_init(
            \Amasty\Reports\Model\Notification::class,
            \Amasty\Reports\Model\ResourceModel\Notification::class
        );
    }
}
