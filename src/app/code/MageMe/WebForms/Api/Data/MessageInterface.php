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

namespace MageMe\WebForms\Api\Data;


use Magento\Framework\Exception\LocalizedException;

interface MessageInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'message_id';
    const RESULT_ID = 'result_id';
    const USER_ID = 'user_id';
    const MESSAGE = 'message';
    const AUTHOR = 'author';
    const IS_CUSTOMER_EMAILED = 'is_customer_emailed';
    const CREATED_AT = 'created_at';
    const IS_FROM_CUSTOMER = 'is_from_customer';
    const IS_READ = 'is_read';
    /**#@-*/

    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set ID
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get resultId
     *
     * @return int|null
     */
    public function getResultId(): ?int;

    /**
     * Set resultId
     *
     * @param int $resultId
     * @return $this
     */
    public function setResultId(int $resultId): MessageInterface;

    /**
     * Get userId
     *
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * Set userId
     *
     * @param int|null $userId
     * @return $this
     */
    public function setUserId(?int $userId): MessageInterface;

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Set message
     *
     * @param string|null $message
     * @return $this
     */
    public function setMessage(?string $message): MessageInterface;

    /**
     * Get author
     *
     * @return string|null
     */
    public function getAuthor(): ?string;

    /**
     * Set author
     *
     * @param string|null $author
     * @return $this
     */
    public function setAuthor(?string $author): MessageInterface;

    /**
     * Get isCustomerEmailed
     *
     * @return bool
     */
    public function getIsCustomerEmailed(): bool;

    /**
     * Set isCustomerEmailed
     *
     * @param bool $isCustomerEmailed
     * @return $this
     */
    public function setIsCustomerEmailed(bool $isCustomerEmailed): MessageInterface;

    /**
     * Get createdTime
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set createdTime
     *
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?string $createdAt): MessageInterface;

    /**
     * Get isFromCustomer
     *
     * @return bool
     */
    public function getIsFromCustomer(): bool;

    /**
     * Set isFromCustomer
     *
     * @param bool $isFromCustomer
     * @return $this
     */
    public function setIsFromCustomer(bool $isFromCustomer): MessageInterface;

    /**
     * Get isRead
     *
     * @return bool
     */
    public function getIsRead(): bool;

    /**
     * Set isRead
     *
     * @param bool $isRead
     * @return $this
     */
    public function setIsRead(bool $isRead): MessageInterface;

    /**
     * Get template variables
     *
     * @return array
     */
    public function getTemplateVars(): array;

    /**
     * Get attached files
     *
     * @return FileMessageInterface[]
     * @throws LocalizedException
     */
    public function getFiles(): array;

    /**
     * Get result
     *
     * @return ResultInterface
     */
    public function getResult(): ResultInterface;

}
