<?php

namespace MageMe\WebForms\Controller\Customer\Message;

use Exception;
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Api\TmpFileMessageRepositoryInterface;
use MageMe\WebForms\Config\Options\Result\Permission;
use MageMe\WebForms\Controller\AbstractAction;
use MageMe\WebForms\File\MessageUploader;
use MageMe\WebForms\Mail\MessageNotification;
use MageMe\WebForms\Model\MessageFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Save extends AbstractAction
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;
    /**
     * @var MessageFactory
     */
    private $messageFactory;
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;
    /**
     * @var TmpFileMessageRepositoryInterface
     */
    private $tmpFileMessageRepository;
    /**
     * @var MessageUploader
     */
    private $uploader;
    /**
     * @var FilterProvider
     */
    private $filterProvider;
    /**
     * @var Header
     */
    private $header;
    /**
     * @var MessageManagerInterface
     */
    private $messageManager;
    /**
     * @var MessageNotification
     */
    private $messageNotification;

    /**
     * @param Header $header
     * @param FilterProvider $filterProvider
     * @param MessageUploader $uploader
     * @param TmpFileMessageRepositoryInterface $tmpFileMessageRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param MessageFactory $messageFactory
     * @param ResultRepositoryInterface $resultRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param SessionFactory $sessionFactory
     * @param MessageManagerInterface $messageManager
     * @param MessageNotification $messageNotification
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Header                            $header,
        FilterProvider                    $filterProvider,
        MessageUploader                   $uploader,
        TmpFileMessageRepositoryInterface $tmpFileMessageRepository,
        MessageRepositoryInterface        $messageRepository,
        MessageFactory                    $messageFactory,
        ResultRepositoryInterface         $resultRepository,
        ScopeConfigInterface              $scopeConfig,
        SessionFactory                    $sessionFactory,
        MessageManagerInterface           $messageManager,
        MessageNotification               $messageNotification,
        Context                           $context,
        PageFactory                       $pageFactory
    )
    {
        parent::__construct($context, $pageFactory);
        $this->session                  = $sessionFactory->create();
        $this->scopeConfig              = $scopeConfig;
        $this->resultRepository         = $resultRepository;
        $this->messageFactory           = $messageFactory;
        $this->messageRepository        = $messageRepository;
        $this->tmpFileMessageRepository = $tmpFileMessageRepository;
        $this->uploader                 = $uploader;
        $this->filterProvider           = $filterProvider;
        $this->header                   = $header;
        $this->messageManager           = $messageManager;
        $this->messageNotification      = $messageNotification;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute()
    {
        $this->session->authenticate();
        $filter   = $this->filterProvider->getPageFilter();
        $resultId = (int)$this->getRequest()->getParam(MessageInterface::RESULT_ID);
        $result   = $this->resultRepository->getById($resultId);
        $form     = $result->getForm();

        if ($result->getCustomerId() != $this->session->getCustomerId()) {
            return $this->redirect('customer/account');
        }
        if (!in_array(Permission::REPLY, $result->getForm()->getCustomerResultPermissionsByResult($result))) {
            return $this->redirect('customer/account');
        }

        $messageContent = (string)$this->getRequest()->getParam(MessageInterface::MESSAGE);
        if (!$messageContent) {
            $this->messageManager->addErrorMessage(__('The reply message is missing.'));
            return $this->response->setRedirect($filter->filter($this->header->getHttpReferer()));
        }

        $attachment = (string)$this->getRequest()->getParam('attachment');

        if ($this->scopeConfig->getValue('webforms/message/nl2br')) {
            $messageContent = str_replace("</p><br>", "</p>", nl2br($messageContent));
        }

        $message = $this->messageFactory->create()
            ->setAuthor($this->session->getCustomer()->getName())
            ->setResultId($result->getId())
            ->setIsFromCustomer(true)
            ->setMessage($messageContent);

        $message = $this->messageRepository->save($message);
        if ($attachment) {
            $this->saveAttachedFiles($message, $attachment);
        }

        $result->setIsReplied(false);
        $this->resultRepository->save($result);

        // send admin notification
        if ($form->getIsAdminNotificationEnabled()) {
            $adminEmail = array_map('trim', explode(',', $form->getAdminNotificationEmail()));
            foreach($adminEmail as $email) {
                $this->messageNotification->sendEmail($message, $email, '', $form->getAdminNotificationBcc(), true);
            }
        }

        $this->messageManager->addSuccessMessage(__('Thank you! Your reply has been sent.'));
        return $this->response->setRedirect($filter->filter($this->header->getHttpReferer()));
    }

    /**
     * @param MessageInterface $message
     * @param string $attachment
     * @throws LocalizedException
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    protected function saveAttachedFiles(MessageInterface $message, string $attachment)
    {
        $hashArray = explode(';', $attachment);
        if (!$hashArray || !is_array($hashArray)) {
            return;
        }
        foreach ($hashArray as $hash) {
            $tmpFile = $this->tmpFileMessageRepository->getByHash($hash);
            if ($tmpFile->getId()) {
                $this->uploader->copyFileFromTmpDir($tmpFile, $message);
                $this->tmpFileMessageRepository->delete($tmpFile);
            }
        }
    }
}
