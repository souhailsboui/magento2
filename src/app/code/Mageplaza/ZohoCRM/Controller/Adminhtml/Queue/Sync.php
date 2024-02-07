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

namespace Mageplaza\ZohoCRM\Controller\Adminhtml\Queue;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\ZohoCRM\Helper\Sync as HelperSync;

/**
 * Class Sync
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Queue
 */
class Sync extends Action
{
    /**
     * @var HelperSync
     */
    protected $helperSync;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Sync constructor.
     *
     * @param Context $context
     * @param HelperSync $helperSync
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        HelperSync $helperSync,
        JsonFactory $resultJsonFactory
    ) {
        $this->helperSync        = $helperSync;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $ids    = $this->getRequest()->getParam('ids');
        $result = [];

        try {
            $totalSuccess     = $this->helperSync->syncByIds($ids);
            $result['status'] = true;
            $result['total']  = $totalSuccess;
        } catch (Exception $e) {
            $result['status']  = false;
            $result['message'] = $e->getMessage();
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }
}
