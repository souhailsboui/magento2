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

namespace Mageplaza\StoreCredit\Controller\Adminhtml\Transaction;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Mageplaza\StoreCredit\Controller\Adminhtml\AbstractTransaction;

/**
 * Class View
 * @package Mageplaza\StoreCredit\Controller\Adminhtml\Transaction
 */
class View extends AbstractTransaction
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $objId = $this->getRequest()->getParam('id');
        $pageTitle = $objId ? __('View Transaction #%1', $objId) : __('Create New Transaction');
        $transaction = $this->_initTransaction();
        if ($transaction) {
            $resultPage = $this->_initAction();
            $resultPage->getConfig()->getTitle()->prepend($pageTitle);

            return $resultPage;
        }

        return $this->_redirect('*/*/');
    }
}
