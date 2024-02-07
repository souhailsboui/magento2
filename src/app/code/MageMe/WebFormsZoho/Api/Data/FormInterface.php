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

namespace MageMe\WebFormsZoho\Api\Data;

interface FormInterface extends \MageMe\WebForms\Api\Data\FormInterface
{
    /** Zoho CRM settings */
    const ZOHO_CRM_IS_LEAD_ENABLED = 'zoho_crm_is_lead_enabled';
    const ZOHO_CRM_EMAIL_FIELD_ID = 'zoho_crm_email_field_id';
    const ZOHO_CRM_LEAD_OWNER = 'zoho_crm_lead_owner';
    const ZOHO_CRM_LEAD_SOURCE = 'zoho_crm_lead_source';
    const ZOHO_CRM_MAP_FIELDS_SERIALIZED = 'zoho_crm_map_fields_serialized';

    /** Zoho Desk settings */
    const ZOHO_DESK_IS_TICKET_ENABLED = 'zoho_desk_is_ticket_enabled';
    const ZOHO_DESK_EMAIL_FIELD_ID = 'zoho_desk_email_field_id';
    const ZOHO_DESK_CONTACT_ID = 'zoho_desk_contact_id';
    const ZOHO_DESK_DEPARTMENT_ID = 'zoho_desk_department_id';
    const ZOHO_DESK_TICKET_STATUS = 'zoho_desk_ticket_status';
    const ZOHO_DESK_TICKET_OWNER = 'zoho_desk_ticket_owner';
    const ZOHO_DESK_TICKET_CHANNEL = 'zoho_desk_ticket_channel';
    const ZOHO_DESK_TICKET_CLASSIFICATION = 'zoho_desk_ticket_classification';
    const ZOHO_DESK_TICKET_PRIORITY = 'zoho_desk_ticket_priority';
    const ZOHO_DESK_TICKET_LANGUAGE = 'zoho_desk_ticket_language';
    const ZOHO_DESK_MAP_FIELDS_SERIALIZED = 'zoho_desk_map_fields_serialized';

    /**
     * Additional constants for keys of data array.
     */
    const ZOHO_CRM_MAP_FIELDS = 'zoho_crm_map_fields';
    const ZOHO_DESK_MAP_FIELDS = 'zoho_desk_map_fields';

    #region Zoho CRM
    /**
     * Get zohoCrmIsLeadEnabled
     *
     * @return bool
     */
    public function getZohoCrmIsLeadEnabled(): bool;

    /**
     * Set zohoCrmIsLeadEnabled
     *
     * @param bool $zohoCrmIsLeadEnabled
     * @return $this
     */
    public function setZohoCrmIsLeadEnabled(bool $zohoCrmIsLeadEnabled): FormInterface;

    /**
     * Get zohoCrmEmailFieldId
     *
     * @return int|null
     */
    public function getZohoCrmEmailFieldId(): ?int;

    /**
     * Set zohoCrmEmailFieldId
     *
     * @param int|null $zohoCrmEmailFieldId
     * @return $this
     */
    public function setZohoCrmEmailFieldId(?int $zohoCrmEmailFieldId): FormInterface;

    /**
     * Get zohoLeadOwner
     *
     * @return string|null
     */
    public function getZohoCrmLeadOwner(): ?string;

    /**
     * Set zohoLeadOwner
     *
     * @param string $zohoCrmLeadOwner
     * @return $this
     */
    public function setZohoCrmLeadOwner(string $zohoCrmLeadOwner): FormInterface;

    /**
     * Get zohoLeadSource
     *
     * @return string|null
     */
    public function getZohoCrmLeadSource(): ?string;

    /**
     * Set zohoLeadSource
     *
     * @param string $zohoCrmLeadSource
     * @return $this
     */
    public function setZohoCrmLeadSource(string $zohoCrmLeadSource): FormInterface;

    /**
     * Get zohoMapFieldsSerialized
     *
     * @return string|null
     */
    public function getZohoCrmMapFieldsSerialized(): ?string;

    /**
     * Set zohoMapFieldsSerialized
     *
     * @param string $zohoCrmMapFieldsSerialized
     * @return $this
     */
    public function setZohoCrmMapFieldsSerialized(string $zohoCrmMapFieldsSerialized): FormInterface;

    /**
     * Get zohoMapFields
     *
     * @return array
     */
    public function getZohoCrmMapFields(): array;

    /**
     * Set zohoMapFields
     *
     * @param array $zohoCrmMapFields
     * @return $this
     */
    public function setZohoCrmMapFields(array $zohoCrmMapFields): FormInterface;
    #endregion

    #regiom Zoho Desk
    /**
     * Get zohoDeskIsTicketEnabled
     *
     * @return bool
     */
    public function getZohoDeskIsTicketEnabled(): bool;

    /**
     * Set zohoDeskIsTicketEnabled
     *
     * @param bool $zohoDeskIsTicketEnabled
     * @return $this
     */
    public function setZohoDeskIsTicketEnabled(bool $zohoDeskIsTicketEnabled): FormInterface;

    /**
     * Get zohoDeskEmailFieldId
     *
     * @return int|null
     */
    public function getZohoDeskEmailFieldId(): ?int;

    /**
     * Set zohoDeskEmailFieldId
     *
     * @param int|null $zohoDeskEmailFieldId
     * @return $this
     */
    public function setZohoDeskEmailFieldId(?int $zohoDeskEmailFieldId): FormInterface;

    /**
     * Get zohoDeskContactID
     *
     * @return string|null
     */
    public function getZohoDeskContactId(): ?string;

    /**
     * Set zohoDeskContactID
     *
     * @param string $zohoDeskContactID
     * @return $this
     */
    public function setZohoDeskContactId(string $zohoDeskContactID): FormInterface;

    /**
     * Get zohoDeskDepartmentId
     *
     * @return string|null
     */
    public function getZohoDeskDepartmentId(): ?string;

    /**
     * Set zohoDeskDepartmentId
     *
     * @param string $zohoDeskDepartmentId
     * @return $this
     */
    public function setZZohoDeskDepartmentId(string $zohoDeskDepartmentId): FormInterface;

    /**
     * Get zohoDeskTicketStatus
     *
     * @return string|null
     */
    public function getZohoDeskTicketStatus(): ?string;

    /**
     * Set zohoDeskTicketStatus
     *
     * @param string $zohoDeskTicketStatus
     * @return $this
     */
    public function setZohoDeskTicketStatus(string $zohoDeskTicketStatus): FormInterface;

    /**
     * Get zohoDeskTicketOwner
     *
     * @return string|null
     */
    public function getZohoDeskTicketOwner(): ?string;

    /**
     * Set zohoDeskTicketOwner
     *
     * @param string $zohoDeskTicketOwner
     * @return $this
     */
    public function setZohoDeskTicketOwner(string $zohoDeskTicketOwner): FormInterface;

    /**
     * Get zohoDeskTicketChannel
     *
     * @return string|null
     */
    public function getZohoDeskTicketChannel(): ?string;

    /**
     * Set zohoDeskTicketChannel
     *
     * @param string $zohoDeskTicketChannel
     * @return $this
     */
    public function setZohoDeskTicketChannel(string $zohoDeskTicketChannel): FormInterface;

    /**
     * Get zohoDeskTicketClassification
     *
     * @return string|null
     */
    public function getZohoDeskTicketClassification(): ?string;

    /**
     * Set zohoDeskTicketClassification
     *
     * @param string $zohoDeskTicketClassification
     * @return $this
     */
    public function setZohoDeskTicketClassification(string $zohoDeskTicketClassification): FormInterface;

    /**
     * Get zohoDeskTicketPriority
     *
     * @return string|null
     */
    public function getZohoDeskTicketPriority(): ?string;

    /**
     * Set zohoDeskTicketPriority
     *
     * @param string $zohoDeskTicketPriority
     * @return $this
     */
    public function setZohoDeskTicketPriority(string $zohoDeskTicketPriority): FormInterface;

    /**
     * Get zohoDeskTicketLanguage
     *
     * @return string|null
     */
    public function getZohoDeskTicketLanguage(): ?string;

    /**
     * Set zohoDeskTicketLanguage
     *
     * @param string $zohoDeskTicketLanguage
     * @return $this
     */
    public function setZohoDeskTicketLanguage(string $zohoDeskTicketLanguage): FormInterface;


    /**
     * Get zohoDeskMapFieldsSerialized
     *
     * @return string|null
     */
    public function getZohoDeskMapFieldsSerialized(): ?string;

    /**
     * Set zohoDeskMapFieldsSerialized
     *
     * @param string $zohoDeskMapFieldsSerialized
     * @return $this
     */
    public function setZohoDeskMapFieldsSerialized(string $zohoDeskMapFieldsSerialized): FormInterface;

    /**
     * Get zohoDeskMapFields
     *
     * @return array
     */
    public function getZohoDeskMapFields(): array;

    /**
     * Set zohoDeskMapFields
     *
     * @param array $zohoDeskMapFields
     * @return $this
     */
    public function setZohoDeskMapFields(array $zohoDeskMapFields): FormInterface;
    #endregion
}
