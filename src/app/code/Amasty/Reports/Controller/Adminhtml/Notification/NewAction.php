<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Controller\Adminhtml\Notification;

use Amasty\Reports\Controller\Adminhtml\Notification;

class NewAction extends Notification
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        // phpcs:ignore Magento2.Legacy.ObsoleteResponse.ForwardResponseMethodFound
        $this->_forward('edit');
    }
}
