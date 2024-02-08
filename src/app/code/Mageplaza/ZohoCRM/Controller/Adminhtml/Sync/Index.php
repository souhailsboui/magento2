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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\ZohoCRM\Helper\Data;

/**
 * Class Index
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Sync
 */
class Index extends Action
{
    const ADMIN_RESOURCE = 'Mageplaza_ZohoCRM::sync_rules';

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Data $helperData
    ) {
        $this->pageFactory = $pageFactory;
        $this->helperData  = $helperData;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        if ($this->helperData->isEnableReportModule()) {
            $resultPage->addHandle('store_switcher');
        }

        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__('Manage Sync Rules'), __('Manage Sync Rules'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Synchronization Rules'));

        return $resultPage;
    }
}
