<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Controller\Adminhtml\Sync;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\ZohoCRM\Controller\Adminhtml\AbstractSync;

/**
 * Class MassStatus
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Sync
 */
class MassStatus extends AbstractSync
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->syncFactory->create()->getCollection());
        $status     = $this->getRequest()->getParam('status');

        $count = 0;
        foreach ($collection->getItems() as $item) {
            $item->setStatus($status)->save();
            $count++;
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $count));

        return $this->_redirect('*/*/');
    }
}
