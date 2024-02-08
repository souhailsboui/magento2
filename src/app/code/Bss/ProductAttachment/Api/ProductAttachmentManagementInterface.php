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
 * Product Attachment Management Interface
 */
interface ProductAttachmentManagementInterface
{
    /**
     * Get module configs by store id
     *
     * @param int $store_id
     * @return string[]
     */
    public function getConfigById($store_id);

    /**
     * Get module configs
     *
     * @return string[]
     */
    public function getConfig();
}
