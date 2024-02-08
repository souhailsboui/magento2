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

namespace Mageplaza\ZohoCRM\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\ZohoCRM\Helper\Data as HelperData;
use Mageplaza\ZohoCRM\Helper\Sync as HelperSync;
use Mageplaza\ZohoCRM\Model\QueueFactory;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractQueue
 * @package Mageplaza\ZohoCRM\Observer
 */
abstract class AbstractQueue implements ObserverInterface
{
    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var HelperSync
     */
    protected $helperSync;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AbstractQueue constructor.
     *
     * @param QueueFactory $queueFactory
     * @param HelperSync $helperSync
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        QueueFactory $queueFactory,
        HelperSync $helperSync,
        HelperData $helperData,
        LoggerInterface $logger
    ) {
        $this->queueFactory = $queueFactory;
        $this->helperSync   = $helperSync;
        $this->helperData   = $helperData;
        $this->logger       = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->helperData->isEnabled()) {
                $this->executeAction($observer);
            }
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function executeAction(Observer $observer)
    {
        return $this;
    }
}
