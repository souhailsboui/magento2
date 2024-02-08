<?php

namespace MageMe\WebForms\Controller\File;

use Exception;
use MageMe\WebForms\Controller\AbstractAction;
use MageMe\WebForms\File\MessageUploader;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class MessageUpload extends AbstractAction
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var MessageUploader
     */
    private $uploader;
    /**
     * @var Session
     */
    private $session;

    /**
     * @param SessionFactory $sessionFactory
     * @param MessageUploader $uploader
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        SessionFactory            $sessionFactory,
        MessageUploader       $uploader,
        JsonFactory           $resultJsonFactory,
        Context               $context,
        PageFactory           $pageFactory
    ) {
        parent::__construct($context, $pageFactory);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->uploader          = $uploader;
        $this->session          = $sessionFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = [
            'hash' => '',
            'error' => []
        ];
        if (!$this->session->isLoggedIn()) {
            $result['error'][] = __('Access denied.');
            return $this->getResultJson($result);
        }
        $fileId          = $this->getRequest()->getParam('file_id');
        try {
            $file           = $this->uploader->saveFileToTmpDir($fileId);
            $result['hash'] = $file->getHash();
        } catch (Exception $exception) {
            $result['error'][] = $exception->getMessage();
        }
        return $this->getResultJson($result);
    }

    /**
     * @param array $data
     * @return Json
     */
    private function getResultJson(array $data): Json
    {
        return $this->resultJsonFactory->create()->setJsonData(json_encode($data));
    }
}