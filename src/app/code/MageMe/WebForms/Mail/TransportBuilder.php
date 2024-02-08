<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Mail;


use Laminas\Mime\Message as LaminasMimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessage;

/**
 * The following class provides the ability to attach files to email notifications
 */
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    const ATTACHMENT_BODY = 'attachment_body';
    const ATTACHMENT_TYPE = 'attachment_type';
    const ATTACHMENT_DISPOSITION = 'attachment_disposition';
    const ATTACHMENT_ENCODING = 'attachment_encoding';
    const ATTACHMENT_FILENAME = 'attachment_filename';
    const ATTACHMENT_ID = 'attachment_id';

    /**
     * container for attachments
     * @var array
     */
    protected $parts = [];

    /**
     * @param $subject
     * @return string
     */
    protected function encodedFileName($subject): string
    {
        return sprintf('=?utf-8?B?%s?=', base64_encode((string)$subject));
    }

    /**
     * @param $parts
     * @return $this
     */
    public function setParts($parts): TransportBuilder
    {
        $this->parts = $parts;
        return $this;
    }

    /**
     * @param string $body
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @param string|null $filename
     * @param string|null $id
     * @return $this
     */
    public function createAttachment(
        string $body,
        string $mimeType = Mime::TYPE_OCTETSTREAM,
        string $disposition = Mime::DISPOSITION_ATTACHMENT,
        string $encoding = Mime::ENCODING_BASE64,
        ?string $filename = null,
        ?string $id = null
    ): TransportBuilder {
        $this->parts[] = $this->prepareAttachmentPart($body, $mimeType, $disposition, $encoding, $filename, $id);
        return $this;
    }

    /**
     * @param string $body
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @param string|null $filename
     * @param string|null $id
     * @return Part
     */
    public function prepareAttachmentPart(
        string $body,
        string $mimeType = Mime::TYPE_OCTETSTREAM,
        string $disposition = Mime::DISPOSITION_ATTACHMENT,
        string $encoding = Mime::ENCODING_BASE64,
        ?string $filename = null,
        ?string $id = null
    ): Part {
        $attachment = new Part($body);
        $attachment->type = $mimeType;
        $attachment->disposition = $disposition;
        $attachment->encoding = $encoding;
        $attachment->filename = $filename;
        if ($id) {
            $attachment->id = $id;
        }
        return $attachment;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function prepareMessage(): TransportBuilder
    {
        parent::prepareMessage();

        // add file attachments to the message
        if (count($this->parts)) {
            $mimeMessage = new LaminasMimeMessage();
            $bodyParts = $this->getMessage()->getMessageBody()->getParts();
            $mimeMessage->setParts(array_merge($bodyParts, $this->parts));
            $this->getMessage()->setBody($mimeMessage);
        }
        return $this;
    }

    /**
     * @return mixed|EmailMessage
     */
    public function getMessage()
    {
        return $this->message;
    }
}
