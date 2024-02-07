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
 * @package     Mageplaza_StoreCredit
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\StoreCredit\Controller\Index;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\StoreCredit\Helper\Data;

/**
 * Class Index
 * @package Mageplaza\StoreCredit\Controller\Index
 */
class Index extends AbstractAccount
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Data $helper
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        PageFactory $resultPageFactory
    ) {
        $this->helper = $helper;
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * @return Page
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->helper->isEnabled() || !$this->helper->isEnabledForCustomer()) {
            throw new NotFoundException(__('Store Credit is turned off.'));
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Store Credit'));

        return $resultPage;
    }
}
