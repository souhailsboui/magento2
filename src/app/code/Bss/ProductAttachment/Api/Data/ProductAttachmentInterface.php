<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_ProductAttachment
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttachment\Api\Data;

interface ProductAttachmentInterface
{
    const FILE_ID = 'file_id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const STATUS = 'status';
    const TYPE = 'type';
    const UPLOADED_FILE = 'uploaded_file';
    const SIZE = 'size';
    const STORE_ID = 'store_id';
    const CUSTOMER_GROUP = 'customer_group';
    const LIMIT_TIME = 'limit_time';
    const POSITION = 'position';
    const DOWNLOADED_TIME = 'downloaded_time';
    const SHOW_FOOTER = 'show_footer';

    /**
     * Get file_id
     *
     * @return int
     */
    public function getFileId();

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Get type
     *
     * @return int
     */
    public function getType();

    /**
     * Get upLoad file
     *
     * @return string
     */
    public function getUploadedFile();

    /**
     * Get size
     *
     * @return string
     */
    public function getSize();

    /**
     * Get store id
     *
     * @return string
     */
    public function getStoreId();

    /**
     * Get customer group
     *
     * @return string
     */
    public function getCustomerGroup();

    /**
     * Get limit time
     *
     * @return string
     */
    public function getLimitTime();

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition();

    /**
     * Get downloaded time
     *
     * @return string
     */
    public function getDownloadedTime();

    /**
     * Get show footer
     *
     * @return int
     */
    public function getShowFooter();

    /**
     * Set file_id
     *
     * @param int $file_id
     * @return $this
     */
    public function setFileId($file_id);

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Set type
     *
     * @param int $type
     * @return $this
     */
    public function setType($type);

    /**
     * Set uploaded file
     *
     * @param string $uploaded_file
     * @return $this
     */
    public function setUploadedFile($uploaded_file);

    /**
     * Set size
     *
     * @param string $size
     * @return $this
     */
    public function setSize($size);

    /**
     * Set store id
     *
     * @param string $store_id
     * @return $this
     */
    public function setStoreId($store_id);

    /**
     * Set customer group
     *
     * @param string $customer_group
     * @return $this
     */
    public function setCustomerGroup($customer_group);

    /**
     * Set limit time
     *
     * @param string $limit_time
     * @return $this
     */
    public function setLimitTime($limit_time);

    /**
     * Set position
     *
     * @param string $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Set downloaded time
     *
     * @param string $downloaded_time
     * @return $this
     */
    public function setDownloadedTime($downloaded_time);

    /**
     * Set show footer
     *
     * @param int $show_footer
     * @return $this
     */
    public function setShowFooter($show_footer);
}
