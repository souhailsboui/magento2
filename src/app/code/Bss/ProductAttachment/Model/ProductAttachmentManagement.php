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
namespace Bss\ProductAttachment\Model;

/**
 * Product Attachment Management
 */
class ProductAttachmentManagement implements \Bss\ProductAttachment\Api\ProductAttachmentManagementInterface
{
    /**
     * @var \Bss\ProductAttachment\Helper\Data
     */
    protected $helperData;

    /**
     * Constructor
     *
     * @param \Bss\ProductAttachment\Helper\Data $helperData
     */
    public function __construct(
        \Bss\ProductAttachment\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Get config by id
     *
     * @param int $store_id
     * @return string[]
     */
    public function getConfigById($store_id)
    {
        return $this->getConfig();
    }

    /**
     * Get configs
     *
     * @return string[]
     */
    public function getConfig()
    {
        $result["module_configs"] = [
            "enable" => $this->helperData->enable(),
            "showProductTab" => $this->helperData->showProductTab(),
            "tabTitle" => $this->helperData->tabTitle(),
            "showBlock" => $this->helperData->showBlock(),
            "blockTitle" => $this->helperData->blockTitle(),
            "maxZize" => $this->helperData->maxZize(),
            "showFileSize" => $this->helperData->showFileSize(),
            "showDownloadNumber" => $this->helperData->showDownloadNumber(),
        ];
        return $result;
    }
}
