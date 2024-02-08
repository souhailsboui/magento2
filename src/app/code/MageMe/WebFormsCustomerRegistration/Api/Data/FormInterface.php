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

namespace MageMe\WebFormsCustomerRegistration\Api\Data;

/**
 *
 */
interface FormInterface extends \MageMe\WebForms\Api\Data\FormInterface
{
    /** Customer Registration Settings */
    const CR_IS_REGISTERED_ON_SUBMISSION = 'cr_is_registered_on_submission';
    const CR_IS_CUSTOMER_EMAIL_UNIQUE = 'cr_is_customer_email_unique';
    const CR_DEFAULT_GROUP_ID = 'cr_default_group_id';
    const CR_IS_REGISTERED_ON_APPROVAL = 'cr_is_registered_on_approval';
    const CR_APPROVAL_GROUP_ID = 'cr_approval_group_id';
    const CR_IS_DEFAULT_NOTIFICATION_ENABLED = 'cr_is_default_notification_enabled';
    const CR_MAP_SERIALIZED = 'cr_map_serialized';

    /**
     * Additional constants for keys of data array.
     */
    const CR_MAP = 'cr_map';

    #region Customer Registration
    /**
     * @return bool
     */
    public function getCrIsRegisteredOnSubmission(): bool;

    /**
     * @param bool $isRegisteredOnSubmission
     * @return $this
     */
    public function setCrIsRegisteredOnSubmission(bool $isRegisteredOnSubmission): FormInterface;

    /**
     * @return bool
     */
    public function getCrIsCustomerEmailUnique(): bool;

    /**
     * @param bool $isCustomerEmailUnique
     * @return $this
     */
    public function setCrIsCustomerEmailUnique(bool $isCustomerEmailUnique): FormInterface;

    /**
     * @return int
     */
    public function getCrDefaultGroupId(): ?int;

    /**
     * @param int $defaultGroupId
     * @return $this
     */
    public function setCrDefaultGroupId(int $defaultGroupId): FormInterface;

    /**
     * @return bool
     */
    public function getCrIsRegisteredOnApproval(): bool;

    /**
     * @param bool $isRegisteredOnApproval
     * @return $this
     */
    public function setCrIsRegisteredOnApproval(bool $isRegisteredOnApproval): FormInterface;

    /**
     * @return int
     */
    public function getCrApprovalGroupId(): ?int;

    /**
     * @param int $approvalGroupId
     * @return $this
     */
    public function setCrApprovalGroupId(int $approvalGroupId): FormInterface;

    /**
     * @return bool
     */
    public function getCrIsDefaultNotificationEnabled(): bool;

    /**
     * @param bool $isDefaultNotificationEnabled
     * @return $this
     */
    public function setCrIsDefaultNotificationEnabled(bool $isDefaultNotificationEnabled): FormInterface;

    /**
     *
     * @return string|null
     */
    public function getCrMapSerialized(): ?string;

    /**
     *
     * @param string $mapSerialized
     * @return $this
     */
    public function setCrMapSerialized(string $mapSerialized): FormInterface;

    /**
     * @return array
     */
    public function getCrMap(): array;

    /**
     * @param array $map
     * @return $this
     */
    public function setCrMap(array $map): FormInterface;
    #endregion
}
