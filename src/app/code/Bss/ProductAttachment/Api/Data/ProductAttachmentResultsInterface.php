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

use Magento\Framework\Api\SearchResultsInterface;

interface ProductAttachmentResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Bss\ProductAttachment\Api\Data\ProductAttachmentInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Bss\ProductAttachment\Api\Data\ProductAttachmentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
