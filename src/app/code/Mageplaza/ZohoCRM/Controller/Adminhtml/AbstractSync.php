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

namespace Mageplaza\ZohoCRM\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\ZohoCRM\Model\QueueFactory;
use Mageplaza\ZohoCRM\Model\Sync;
use Mageplaza\ZohoCRM\Model\SyncFactory;

/**
 * Class AbstractSync
 * @package Mageplaza\RewardPoints\Controller\Adminhtml
 */
abstract class AbstractSync extends Action
{
    const ADMIN_RESOURCE = 'Mageplaza_ZohoCRM::sync_rules';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var SyncFactory
     */
    protected $syncFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * AbstractSync constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Filter $filter
     * @param SyncFactory $syncFactory
     * @param JsonFactory $jsonFactory
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Filter $filter,
        SyncFactory $syncFactory,
        JsonFactory $jsonFactory,
        QueueFactory $queueFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry          = $registry;
        $this->filter            = $filter;
        $this->syncFactory       = $syncFactory;
        $this->resultJsonFactory = $jsonFactory;
        $this->queueFactory      = $queueFactory;
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__('Manage Sync Rules'), __('Manage Sync Rules'));

        return $resultPage;
    }

    /**
     * @return Sync|null
     */
    protected function _initSync()
    {
        $syncId    = $this->getRequest()->getParam('id');
        $syncModel = $this->syncFactory->create();
        if ($syncId) {
            $syncModel->load($syncId);
            if (!$syncModel->getId()) {
                $this->messageManager->addErrorMessage(__('This item does not exists.'));

                return null;
            }
        }

        $this->registry->register('sync_rule', $syncModel);

        return $syncModel;
    }
}
