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
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\ZohoCRM\Helper\Sync as HelperSync;
use Mageplaza\ZohoCRM\Model\ResourceModel\Queue\CollectionFactory;

/**
 * Class EstimateSync
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Queue
 */
class EstimateSync extends Action
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
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * EstimateSync constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param HelperSync $helperSync
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        HelperSync $helperSync,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperSync        = $helperSync;
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $type   = $this->getRequest()->getParam('type');
        $result = [];
        try {
            if ($type) {
                if ($type === 'all') {
                    $collection    = $this->filter->getCollection($this->collectionFactory->create());
                    $result['ids'] = $collection->getAllIds();
                } else {
                    $result['ids'] = $this->helperSync->getAllIds($type);
                }

                $result['total'] = count($result['ids']);
                if ($result['total'] === 0) {
                    $result['message'] = __('Data not found when trying to synchronize.');
                }
                $result['status'] = true;
            } else {
                $result['status']  = false;
                $result['message'] = __('Please select type sync.');
            }
        } catch (Exception $e) {
            $result['status']  = false;
            $result['message'] = __($e->getMessage());
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }
}
