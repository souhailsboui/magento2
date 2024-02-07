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

namespace MageMe\WebFormsZoho\Model;

use MageMe\WebFormsZoho\Api\Data\FormInterface;

class Form extends \MageMe\WebForms\Model\Form implements FormInterface
{
    #region DB getters and setters
    /**
     * @inheritDoc
     */
    public function getZohoCrmIsLeadEnabled(): bool
    {
        return (bool)$this->getData(self::ZOHO_CRM_IS_LEAD_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setZohoCrmIsLeadEnabled(bool $zohoCrmIsLeadEnabled): FormInterface
    {
        return $this->setData(self::ZOHO_CRM_IS_LEAD_ENABLED, $zohoCrmIsLeadEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getZohoCrmEmailFieldId(): ?int
    {
        return $this->getData(self::ZOHO_CRM_EMAIL_FIELD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setZohoCrmEmailFieldId(?int $zohoCrmEmailFieldId): FormInterface
    {
        return $this->setData(self::ZOHO_CRM_EMAIL_FIELD_ID, $zohoCrmEmailFieldId);
    }

    /**
     * @inheritDoc
     */
    public function getZohoCrmLeadOwner(): ?string
    {
        return $this->getData(self::ZOHO_CRM_LEAD_OWNER);
    }

    /**
     * @inheritDoc
     */
    public function setZohoCrmLeadOwner(string $zohoCrmLeadOwner): FormInterface
    {
        return $this->setData(self::ZOHO_CRM_LEAD_OWNER, $zohoCrmLeadOwner);
    }

    /**
     * @inheritDoc
     */
    public function getZohoCrmLeadSource(): ?string
    {
        return $this->getData(self::ZOHO_CRM_LEAD_SOURCE);
    }

    /**
     * @inheritDoc
     */
    public function setZohoCrmLeadSource(string $zohoCrmLeadSource): FormInterface
    {
        return $this->setData(self::ZOHO_CRM_LEAD_SOURCE, $zohoCrmLeadSource);
    }

    /**
     * @inheritDoc
     */
    public function getZohoCrmMapFieldsSerialized(): ?string
    {
        return $this->getData(self::ZOHO_CRM_MAP_FIELDS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setZohoCrmMapFieldsSerialized(string $zohoCrmMapFieldsSerialized): FormInterface
    {
        return $this->setData(self::ZOHO_CRM_MAP_FIELDS_SERIALIZED, $zohoCrmMapFieldsSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getZohoCrmMapFields(): array
    {
        $data = $this->getData(self::ZOHO_CRM_MAP_FIELDS);
        return is_array($data) ? $data : [];
    }

    /**
     * @inheritDoc
     */
    public function setZohoCrmMapFields(array $zohoCrmMapFields): FormInterface
    {
        return $this->setData(self::ZOHO_CRM_MAP_FIELDS, $zohoCrmMapFields);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskIsTicketEnabled(): bool
    {
        return (bool)$this->getData(self::ZOHO_DESK_IS_TICKET_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskIsTicketEnabled(bool $zohoDeskIsTicketEnabled): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_IS_TICKET_ENABLED, $zohoDeskIsTicketEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskEmailFieldId(): ?int
    {
        return $this->getData(self::ZOHO_DESK_EMAIL_FIELD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskEmailFieldId(?int $zohoDeskEmailFieldId): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_EMAIL_FIELD_ID, $zohoDeskEmailFieldId);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskContactId(): ?string
    {
        return $this->getData(self::ZOHO_DESK_CONTACT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskContactId(string $zohoDeskContactID): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_CONTACT_ID, $zohoDeskContactID);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskDepartmentId(): ?string
    {
        return $this->getData(self::ZOHO_DESK_DEPARTMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setZZohoDeskDepartmentId(string $zohoDeskDepartmentId): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_DEPARTMENT_ID, $zohoDeskDepartmentId);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskTicketStatus(): ?string
    {
        return $this->getData(self::ZOHO_DESK_TICKET_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskTicketStatus(string $zohoDeskTicketStatus): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_TICKET_STATUS, $zohoDeskTicketStatus);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskTicketOwner(): ?string
    {
        return $this->getData(self::ZOHO_DESK_TICKET_OWNER);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskTicketOwner(string $zohoDeskTicketOwner): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_TICKET_OWNER, $zohoDeskTicketOwner);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskTicketChannel(): ?string
    {
        return $this->getData(self::ZOHO_DESK_TICKET_CHANNEL);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskTicketChannel(string $zohoDeskTicketChannel): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_TICKET_CHANNEL, $zohoDeskTicketChannel);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskTicketClassification(): ?string
    {
        return $this->getData(self::ZOHO_DESK_TICKET_CLASSIFICATION);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskTicketClassification(string $zohoDeskTicketClassification): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_TICKET_CLASSIFICATION, $zohoDeskTicketClassification);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskTicketPriority(): ?string
    {
        return $this->getData(self::ZOHO_DESK_TICKET_PRIORITY);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskTicketPriority(string $zohoDeskTicketPriority): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_TICKET_PRIORITY, $zohoDeskTicketPriority);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskTicketLanguage(): ?string
    {
        return $this->getData(self::ZOHO_DESK_TICKET_LANGUAGE);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskTicketLanguage(string $zohoDeskTicketLanguage): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_TICKET_LANGUAGE, $zohoDeskTicketLanguage);
    }


    /**
     * @inheritDoc
     */
    public function getZohoDeskMapFieldsSerialized(): ?string
    {
        return $this->getData(self::ZOHO_DESK_MAP_FIELDS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskMapFieldsSerialized(string $zohoDeskMapFieldsSerialized): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_MAP_FIELDS_SERIALIZED, $zohoDeskMapFieldsSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getZohoDeskMapFields(): array
    {
        $data = $this->getData(self::ZOHO_DESK_MAP_FIELDS);
        return is_array($data) ? $data : [];
    }

    /**
     * @inheritDoc
     */
    public function setZohoDeskMapFields(array $zohoDeskMapFields): FormInterface
    {
        return $this->setData(self::ZOHO_DESK_MAP_FIELDS, $zohoDeskMapFields);
    }
    #endregion
}
