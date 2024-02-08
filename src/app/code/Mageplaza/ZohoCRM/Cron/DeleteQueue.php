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

namespace Mageplaza\ZohoCRM\Cron;

use Exception;
use Mageplaza\ZohoCRM\Helper\Data as HelperData;
use Mageplaza\ZohoCRM\Model\ResourceModel\Queue;
use Psr\Log\LoggerInterface;

/**
 * Class DeleteQueue
 * @package Mageplaza\ZohoCRM\Cron
 */
class DeleteQueue
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * DeleteQueue constructor.
     *
     * @param Queue $queue
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Queue $queue,
        HelperData $helperData,
        LoggerInterface $logger
    ) {
        $this->queue      = $queue;
        $this->helperData = $helperData;
        $this->logger     = $logger;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if ($this->helperData->isEnabled()) {
            $days = $this->helperData->getDeleteAfter();
            try {
                $this->queue->deleteRecordAfter($days);
            } catch (Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        return $this;
    }
}
