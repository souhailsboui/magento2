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
namespace Bss\ProductAttachment\Controller\Adminhtml;

abstract class File extends \Magento\Backend\App\Action
{

    /**
     * @var \Bss\ProductAttachment\Model\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * Constructor
     *
     * @param \Bss\ProductAttachment\Model\FileFactory $fileFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Bss\ProductAttachment\Model\FileFactory $fileFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_fileFactory          = $fileFactory;
        $this->_coreRegistry          = $coreRegistry;
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($context);
    }

    /**
     * Init File
     *
     * @return \Bss\ProductAttachment\Model\File
     */
    protected function _initFile()
    {
        $fileId  = (int) $this->getRequest()->getParam('file_id');
        /** @var \Bss\ProductAttachment\Model\File $attachment */
        $attachment    = $this->_fileFactory->create();
        if ($fileId) {
            $attachment->load($fileId);
        }
        $this->_coreRegistry->register('bss_productattachment_file', $attachment);
        return $attachment;
    }
}
