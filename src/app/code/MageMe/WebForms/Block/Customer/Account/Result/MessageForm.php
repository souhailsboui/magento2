<?php

namespace MageMe\WebForms\Block\Customer\Account\Result;

use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Ui\Component\Result\Reply\Form\DataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class MessageForm extends Template
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
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @param FieldFactory $fieldFactory
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        FieldFactory $fieldFactory,
        Registry         $registry,
        Template\Context $context,
        array            $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->fieldFactory = $fieldFactory;
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
    public function setResult(ResultInterface $result): MessageForm
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return 'mm-message-form';
    }

    /**
     * @return string
     */
    public function getSubmitUrl(): string
    {
        return $this->getUrl('webforms/customer/message_save', [MessageInterface::RESULT_ID => $this->getResult()->getId()]);
    }

    /**
     * @return string
     */
    public function getDropzoneUrl(): string
    {
        return $this->getUrl('webforms/file/messageUpload');
    }

    /**
     * @return string
     */
    public function getDropzoneId(): string
    {
        return 'field' . $this->getDropzoneUid();
    }

    /**
     * @return string
     */
    public function getDropzoneUid(): string
    {
        return 'mm-message-attachment';
    }

    /**
     * @return string
     */
    public function getDropzoneName(): string
    {
        return 'attachment';
    }

    /**
     * @return Phrase
     */
    public function getDropzoneText(): Phrase
    {
        return __('Add file or drop here');
    }

    /**
     * @return int
     */
    public function getDropzoneMaxFiles(): int
    {
        return DataProvider::DROPZONE_MAX_FILES;
    }

    /**
     * @return int
     */
    public function getDropzoneAllowedSize(): int
    {
        return DataProvider::DROPZONE_FILES_UPLOAD_LIMIT;
    }

    /**
     * @return array
     */
    public function getDropzoneRestrictedExtensions(): array
    {
        try {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            return $this->fieldFactory->create('file')->getRestrictedExtensions();
        } catch (LocalizedException $e) {
            return [];
        }
    }
}