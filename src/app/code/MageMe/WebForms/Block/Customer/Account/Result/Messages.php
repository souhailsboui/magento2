<?php

namespace MageMe\WebForms\Block\Customer\Account\Result;

use MageMe\WebForms\Api\Data\FileMessageInterface;
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Messages extends Template
{
    /**
     * @var bool
     */
    protected $_isScopePrivate = true;
    /**
     * @var ResultInterface|null
     */
    private $result;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;
    /**
     * @var FileMessageRepositoryInterface
     */
    private $fileMessageRepository;

    /**
     * @param FileMessageRepositoryInterface $fileMessageRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param Registry $registry
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        FileMessageRepositoryInterface $fileMessageRepository,
        MessageRepositoryInterface $messageRepository,
        Registry                                $registry,
        Template\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->registry                 = $registry;
        $this->messageRepository = $messageRepository;
        $this->fileMessageRepository = $fileMessageRepository;
    }

    /**
     * @return ResultInterface|null
     */
    public function getResult(): ?ResultInterface
    {
        if (!$this->result) {
            $this->result = $this->registry->registry('webforms_result');
        }
        return $this->result;
    }

    /**
     * @param ResultInterface $result
     * @return $this
     */
    public function setResult(ResultInterface $result): Messages
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return array|MessageInterface[]
     */
    public function getMessages(): array
    {
        $result = $this->getResult();
        if (!$result || !$result->getId()) {
            return [];
        }
        return $this->messageRepository->getListByResultId($result->getId())->getItems();
    }

    /**
     * @param MessageInterface $message
     * @return array|FileMessageInterface[]
     */
    public function getMessageFiles(MessageInterface $message): array
    {
        return $this->fileMessageRepository->getListByMessageId($message->getId())->getItems();
    }
}