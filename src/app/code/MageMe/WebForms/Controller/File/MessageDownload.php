<?php

namespace MageMe\WebForms\Controller\File;

use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use MageMe\WebForms\Controller\AbstractAction;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;

class MessageDownload extends AbstractAction
{
    /**
     * @var FileMessageRepositoryInterface
     */
    private $fileMessageRepository;
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     * @param SessionFactory $sessionFactory
     * @param MessageRepositoryInterface $messageRepository
     * @param FileMessageRepositoryInterface $fileMessageRepository
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(Filesystem                     $filesystem,
                                SessionFactory                 $sessionFactory,
                                MessageRepositoryInterface     $messageRepository,
                                FileMessageRepositoryInterface $fileMessageRepository,
                                Context                        $context,
                                PageFactory                    $pageFactory
    ) {
        parent::__construct($context, $pageFactory);
        $this->fileMessageRepository = $fileMessageRepository;
        $this->messageRepository     = $messageRepository;
        $this->session               = $sessionFactory->create();
        $this->filesystem            = $filesystem;
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    public function execute()
    {
        $hash = $this->getRequest()->getParam('hash');
        if (!$hash) {
            return $this->forwardNoRoute();
        }
        try {
            $file    = $this->fileMessageRepository->getByHash($hash);
            $message = $this->messageRepository->getById($file->getMessageId());
            $result  = $message->getResult();
        } catch (NoSuchEntityException $e) {
            return $this->forwardNoRoute();
        }
        if ($result->getCustomerId() != $this->session->getCustomerId()) {
            return $this->forwardNoRoute();
        }
        if (!file_exists($file->getFullPath())) {
            return $this->forwardNoRoute();
        }
        $fileName    = $file->getName();
        $contentType = $file->getMimeType();
        $this->getResponse()->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );
        if ($fileSize = $file->getSize()) {
            $this->getResponse()->setHeader('Content-Length', $fileSize);
        }
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();

        $handle = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->openFile($file->getPath());
        $file   = "";
        while (true == ($buffer = $handle->read(1024))) {
            $file .= $buffer;
        }
        return $this->getResponse()->setBody($file);
    }

    /**
     * @return Forward
     */
    private function forwardNoRoute(): Forward
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('noroute');
        return $resultForward;
    }
}
