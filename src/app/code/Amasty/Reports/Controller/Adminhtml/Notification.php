<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Notification extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_Reports::notification';
}
