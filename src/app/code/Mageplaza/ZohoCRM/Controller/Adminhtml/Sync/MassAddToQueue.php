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

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\ZohoCRM\Controller\Adminhtml\AbstractSync;
use Mageplaza\ZohoCRM\Model\Queue;
use Mageplaza\ZohoCRM\Model\Source\Status;
use Mageplaza\ZohoCRM\Model\Sync;

/**
 * Class MassAddToQueue
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Sync
 */
class MassAddToQueue extends AbstractSync
{
    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection(
            $this->syncFactory->create()->getCollection()
                ->addFieldToFilter('status', Status::ACTIVE)
                ->setOrder('priority', 'ASC')
        );

        /**
         * @var Queue $queue
         */
        $queue = $this->queueFactory->create();
        try {
            $count = 0;
            foreach ($collection->getItems() as $sync) {
                /**
                 * @var Sync $sync
                 */
                $count += $queue->addToQueue($sync);
            }

            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been added.', $count));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('*/*/');
    }
}
