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

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\ZohoCRM\Controller\Adminhtml\AbstractSync;

/**
 * Class Edit
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Sync
 */
class Edit extends AbstractSync
{
    /**
     * @return Page|ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $sync = $this->_initSync();
        $this->_session->unsMpZohoMagentoObject();
        if ($sync) {
            /** @var Page $resultPage */
            $resultPage = $this->_initAction();

            $resultPage->getConfig()
                ->getTitle()->prepend($sync->getId() ?
                    __('Edit Synchronization Rule #%1', $sync->getId()) : __('Create Synchronization Rule'));

            return $resultPage;
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
