<?php

namespace MageMe\WebForms\Api\Data;

interface StatisticsInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const ENTITY_ID = 'entity_id';
    const ENTITY_TYPE = 'entity_type';
    const CODE = 'code';
    const VALUE = 'value';
    /**#@-*/

    /**
     * Get id
     *
     * @return mixed
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getId();

    /**
     * Set id
     *
     * @param mixed $id
     * @return $this
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpMissingParamTypeInspection
     */
    public function setId($id);

    /**
     * Get entityId
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Set entityId
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId(int $entityId): StatisticsInterface;

    /**
     * Get entityType
     *
     * @return string|null
     */
    public function getEntityType(): ?string;

    /**
     * Set entityType
     *
     * @param string $entityType
     * @return $this
     */
    public function setEntityType(string $entityType): StatisticsInterface;

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): StatisticsInterface;

    /**
     * Get value
     *
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * Set value
     *
     * @param string|null $value
     * @return $this
     */
    public function setValue(?string $value): StatisticsInterface;
}