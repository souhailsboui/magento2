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
namespace Bss\ProductAttachment\Api;

/**
 * Product Attachment Repository Interface
 */
interface ProductAttachmentRepositoryInterface
{
    /**
     * Get by id
     *
     * @param int $file_id
     * @return \Bss\ProductAttachment\Api\Data\ProductAttachmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($file_id);

    /**
     * Get list product attachment
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Bss\ProductAttachment\Api\Data\ProductAttachmentResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Save product attachment
     *
     * @param \Bss\ProductAttachment\Api\Data\ProductAttachmentInterface $productAttachment
     * @return \Bss\ProductAttachment\Api\Data\ProductAttachmentInterface
     */
    public function save(\Bss\ProductAttachment\Api\Data\ProductAttachmentInterface $productAttachment);

    /**
     * Delete product attachment
     *
     * @param int $file_id
     * @return \Bss\ProductAttachment\Api\Data\ProductAttachmentInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function deleteById($file_id);

    /**
     * Download time attachment
     *
     * @param int $file_id
     * @return \Bss\ProductAttachment\Api\Data\ProductAttachmentInterface
     */
    public function downloadTime($file_id);
}
