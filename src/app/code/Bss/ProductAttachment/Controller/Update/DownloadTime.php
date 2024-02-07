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
namespace Bss\ProductAttachment\Controller\Update;

class DownloadTime extends \Magento\Framework\App\Action\Action
{
    /**
     * Attachment Factory
     *
     * @var \Bss\ProductAttachment\Model\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\ProductAttachment\Model\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\App\Action\Context $context,
        \Bss\ProductAttachment\Model\FileFactory $fileFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Json|void
     */
    public function execute()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $id = $this->getRequest()->getPost('file_id');
            $attachment = $this->_fileFactory->create()->load($id);
            $currentTime = $attachment->getData('downloaded_time');
            $currentTime ++;
            $attachment->addData(['downloaded_time' => $currentTime]);
            $attachment->save();
            $limit = $attachment->getData('limit_time');
            $result['limited'] = (($limit!= 0) && ($limit - $currentTime == 0))? 'limited' : false;
            $result['downloadtime'] = $currentTime;

            /** @var \Magento\Framework\Controller\Result\Json $response */
            $response = $this->resultJsonFactory->create()->setData($result);
            return $response;

        } else {
            $this->_redirect('no-route');
            return;
        }
    }
}
