<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductAttachment
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttachment\Controller\Adminhtml\File;

use Magento\Backend\Model\Session;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Bss\ProductAttachment\Controller\Adminhtml\File
{

    /**
     * @var Session
     */
    protected $_backendSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Bss\ProductAttachment\Model\FileFactory $fileFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Bss\ProductAttachment\Model\FileFactory $fileFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_backendSession    = $context->getSession();
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($fileFactory, $registry, $context);
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('file_id');
        /** @var \Bss\ProductAttachment\Model\File $file */
        $file = $this->_initFile();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Bss_ProductAttachment::attachment')
            ->getConfig()->getTitle()->set(__('Product Attachment'));
        if ($id) {
            $file->load($id);
            if (!$file->getId()) {
                $this->messageManager->addErrorMessage(__('This Attachment no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'bss_productattachment/*/edit',
                    [
                        'file_id' => $file->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }

        $title = $file->getId()?
            __("%1", $file->getTitle()) :
            __('New Attachment');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_backendSession->getData('bss_productattachment_file_data', true);

        if (!empty($data)) {
            $file->setData($data);
        }
        return $resultPage;
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_ProductAttachment::save");
    }
}
