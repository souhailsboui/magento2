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

namespace MageMe\WebFormsCustomerRegistration\Model;

use MageMe\WebFormsCustomerRegistration\Api\Data\FormInterface;

class Form extends \MageMe\WebForms\Model\Form implements FormInterface
{
    #region DB getters and setters
    /**
     * @inheritDoc
     */
    public function getCrIsRegisteredOnSubmission(): bool
    {
        return (bool)$this->getData(self::CR_IS_REGISTERED_ON_SUBMISSION);
    }

    /**
     * @inheritDoc
     */
    public function setCrIsRegisteredOnSubmission(bool $isRegisteredOnSubmission): FormInterface
    {
        return $this->setData(self::CR_IS_REGISTERED_ON_SUBMISSION, $isRegisteredOnSubmission);
    }

    /**
     * @inheritDoc
     */
    public function getCrIsCustomerEmailUnique(): bool
    {
        return (bool)$this->getData(self::CR_IS_CUSTOMER_EMAIL_UNIQUE);
    }

    /**
     * @inheritDoc
     */
    public function setCrIsCustomerEmailUnique(bool $isCustomerEmailUnique): FormInterface
    {
        return $this->setData(self::CR_IS_CUSTOMER_EMAIL_UNIQUE, $isCustomerEmailUnique);
    }

    /**
     * @inheritDoc
     */
    public function getCrDefaultGroupId(): ?int
    {
        return $this->getData(self::CR_DEFAULT_GROUP_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCrDefaultGroupId(int $defaultGroupId): FormInterface
    {
        return $this->setData(self::CR_DEFAULT_GROUP_ID, $defaultGroupId);
    }

    /**
     * @inheritDoc
     */
    public function getCrIsRegisteredOnApproval(): bool
    {
        return (bool)$this->getData(self::CR_IS_REGISTERED_ON_APPROVAL);
    }

    /**
     * @inheritDoc
     */
    public function setCrIsRegisteredOnApproval(bool $isRegisteredOnApproval): FormInterface
    {
        return $this->setData(self::CR_IS_REGISTERED_ON_APPROVAL, $isRegisteredOnApproval);
    }

    /**
     * @inheritDoc
     */
    public function getCrApprovalGroupId(): ?int
    {
        return $this->getData(self::CR_APPROVAL_GROUP_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCrApprovalGroupId(int $approvalGroupId): FormInterface
    {
        return $this->setData(self::CR_APPROVAL_GROUP_ID, $approvalGroupId);
    }

    /**
     * @inheritDoc
     */
    public function getCrIsDefaultNotificationEnabled(): bool
    {
        return (bool)$this->getData(self::CR_IS_DEFAULT_NOTIFICATION_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setCrIsDefaultNotificationEnabled(bool $isDefaultNotificationEnabled): FormInterface
    {
        return $this->setData(self::CR_IS_DEFAULT_NOTIFICATION_ENABLED, $isDefaultNotificationEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getCrMapSerialized(): ?string
    {
        return $this->getData(self::CR_MAP_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setCrMapSerialized(string $mapSerialized): FormInterface
    {
        return $this->setData(self::CR_MAP_SERIALIZED, $mapSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getCrMap(): array
    {
        $data = $this->getData(self::CR_MAP);
        return is_array($data) ? $data : [];
    }

    /**
     * @inheritDoc
     */
    public function setCrMap(array $map): FormInterface
    {
        return $this->setData(self::CR_MAP, $map);
    }
    #endregion

}
