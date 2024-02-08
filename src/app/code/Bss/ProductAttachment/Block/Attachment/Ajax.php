<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductAttachment
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttachment\Block\Attachment;

class Ajax extends \Magento\Framework\View\Element\Template
{
    /**
     * Attachment Type Link values
     */
    const LINK_ATTACHMENT = 0;

    /**
     * Attachment List
     *
     * @var array
     */
    protected $_attachments;

    /**
     * @var string
     */
    protected $_storeId;

    /**
     * @var string
     */
    protected $_customerGroupId;

    /**
     * Repository
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Bss\ProductAttachment\Helper\Data
     */
    protected $_helper;

    /**
     * Ajax constructor.
     * @param \Bss\ProductAttachment\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Bss\ProductAttachment\Helper\Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_assetRepo = $context->getAssetRepository();
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Set Attachments
     *
     * @param array $attachments
     * @return void
     */
    public function setAttachments($attachments)
    {
        $this->_attachments = $attachments;
    }

    /**
     * Get attachments
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->_attachments;
    }

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    /**
     * Get attachments
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Set Customer Group Id
     *
     * @param int $customerGroupId
     * @return void
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->_customerGroupId = $customerGroupId;
    }

    /**
     * Get attachments
     *
     * @return string
     */
    public function getCustomerGroupId()
    {
        return $this->_customerGroupId;
    }

    /**
     * Check In Store View
     *
     * @param string|mixed $attachmentStoreView
     * @param string $storeId
     * @return bool
     */
    public function inStoreView($attachmentStoreView, $storeId)
    {
        if ($attachmentStoreView !== null) {
            $listStoreView = explode(",", $attachmentStoreView);
        } else {
            $listStoreView = [];
        }

        if (in_array($storeId, $listStoreView)
            || in_array(0, $listStoreView)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check In Customer Group
     *
     * @param string|mixed $attachmentCustomerGroup
     * @param string $customerGroupId
     * @return bool
     */
    public function inCustomerGroup($attachmentCustomerGroup, $customerGroupId)
    {
        if ($attachmentCustomerGroup !== null) {
            $listCustomerGroup = explode(",", $attachmentCustomerGroup);
        } else {
            $listCustomerGroup = [];
        }

        if (in_array($customerGroupId, $listCustomerGroup)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attachment Limit
     *
     * @param String $downloadtime
     * @param String $limitTime
     * @return bool
     */
    public function unlimit($downloadtime, $limitTime)
    {
        if ($limitTime == 0 || $downloadtime < $limitTime) {
            return true;
        }
        return false;
    }

    /**
     * Get Url Icon
     *
     * @param String $iconType
     * @return string
     */
    public function getIconUrl($iconType)
    {
        $exts = [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'tiff',
                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'ppt',
                    'pptx',
                    'mp3',
                    'avi',
                    'mp4',
                    'zip',
                    'rar',
                    'txt',
                    'ini',
                ];
        $exten = pathinfo($iconType, PATHINFO_EXTENSION);
        $exten = strtolower($exten);
        $exten = (!in_array($exten, $exts))? "default" : $exten;

        return $this->_assetRepo->getUrl("Bss_ProductAttachment::images/{$exten}.png");
    }

    /**
     * Get Url Icon Link
     *
     * @return string
     */
    public function getLinkIcon()
    {
        return $this->_assetRepo->getUrl("Bss_ProductAttachment::images/link.png");
    }

    /**
     * Get Attachment Helper
     *
     * @return \Bss\ProductAttachment\Helper\Data
     */
    public function getHepler()
    {
        return $this->_helper;
    }
}
