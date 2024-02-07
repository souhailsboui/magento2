<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Rule extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_Reports::rule';
}
