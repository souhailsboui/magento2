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


interface TmpFileDropzoneInterface
{

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'tmp_file_dropzone_id';
    const FIELD_ID = 'field_id';
    const NAME = 'name';
    const SIZE = 'size';
    const MIME_TYPE = 'mime_type';
    const PATH = 'path';
    const HASH = 'hash';
    const CREATED_AT = 'created_at';
    /**#@-*/

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set id
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get fieldId
     *
     * @return int|null
     */
    public function getFieldId(): ?int;

    /**
     * Set fieldId
     *
     * @param int $fieldId
     * @return $this
     */
    public function setFieldId(int $fieldId): TmpFileDropzoneInterface;

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): TmpFileDropzoneInterface;

    /**
     * Get size
     *
     * @return int|null
     */
    public function getSize(): ?int;

    /**
     * Set size
     *
     * @param int|null $size
     * @return $this
     */
    public function setSize(?int $size): TmpFileDropzoneInterface;

    /**
     * Get mimeType
     *
     * @return string|null
     */
    public function getMimeType(): ?string;

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType(string $mimeType): TmpFileDropzoneInterface;

    /**
     * Get path
     *
     * @return string|null
     */
    public function getPath(): ?string;

    /**
     * Set path
     *
     * @param string $path
     * @return $this
     */
    public function setPath(string $path): TmpFileDropzoneInterface;

    /**
     * Get hash
     *
     * @return string|null
     */
    public function getHash(): ?string;

    /**
     * Set hash
     *
     * @param string $hash
     * @return $this
     */
    public function setHash(string $hash): TmpFileDropzoneInterface;

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
    public function setCreatedAt(?string $createdAt): TmpFileDropzoneInterface;

}
