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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttachment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    const XML_PATH_ENABLED = 'attachment/general/enable';
    const XML_PATH_SHOW_PRODUCT_TAB = 'attachment/general/show_product_tab';
    const XML_PATH_TAB_TITLE = 'attachment/general/tab_title';
    const XML_PATH_SHOW_BLOCK = 'attachment/general/show_block';
    const XML_PATH_BLOCK_TITLE = 'attachment/general/block_title';
    const XML_PATH_MAX_SIZE = 'attachment/general/max_size';
    const XML_PATH_SHOW_FILE_SIZE = 'attachment/general/show_file_size';
    const XML_PATH_SHOW_DOWNLOAD_NUMBER = 'attachment/general/show_download_number';

    /**
     * Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Scope
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * File Factory
     *
     * @var \Bss\ProductAttachment\Model\FileFactory $fileFactory
     */
    protected $_attachmentFactory;

    /**
     * Url Interface
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * Product Factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Bss\ProductAttachment\Model\FileFactory $fileFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Bss\ProductAttachment\Model\FileFactory $fileFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->customerSession = $customerSession;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_attachmentFactory = $fileFactory;
        $this->_backendUrl = $backendUrl;
        $this->_product = $productFactory;
        parent::__construct($context);
    }

    /**
     * Is enable module
     *
     * @param null|int $storeId
     * @return string
     */
    public function enable($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Show product tab
     *
     * @param null|int $storeId
     * @return string
     */
    public function showProductTab($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_PRODUCT_TAB,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Tab title
     *
     * @param null|int $storeId
     * @return string
     */

    public function tabTitle($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TAB_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Show block
     *
     * @param null|int $storeId
     * @return string
     */
    public function showBlock($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Block title
     *
     * @param null|int $storeId
     * @return string
     */
    public function blockTitle($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_BLOCK_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Max size
     *
     * @param null|int $storeId
     * @return string
     */
    public function maxZize($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Show file size
     *
     * @param null|int $storeId
     * @return string
     */
    public function showFileSize($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_FILE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is show download number
     *
     * @param null|int $storeId
     * @return string
     */
    public function showDownloadNumber($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_DOWNLOAD_NUMBER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get attachment by id
     *
     * @param int $id
     * @return array
     */
    public function getDataAttachmentById($id = 0)
    {
        $attachment = $this->_attachmentFactory->create()->load($id);
        $result = $attachment->getData();
        return $result;
    }

    /**
     * Get product max file upload
     *
     * @return string
     */
    public function getMaxFileSize()
    {
        $maxSizeServer = ini_get("upload_max_filesize");
        $maxSizeServer = $this->convertPHPSizeToBytes($maxSizeServer);

        $maxPostSize = ini_get("post_max_size");
        $maxPostSize = $this->convertPHPSizeToBytes($maxPostSize);

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $maxSizeConfig = $this->_scopeConfig->getValue('attachment/general/max_size', $storeScope);
        $maxSizeConfig *= 1024*1024;
        return min($maxSizeServer, $maxSizeConfig, $maxPostSize);
    }

    /**
     * Convert PHPSize In php.ini To Bytes
     *
     * @param String $size
     * @return bool|int|string
     */
    public function convertPHPSizeToBytes($size)
    {
        if (is_numeric($size)) {
            return $size;
        }
        $sSuffix = substr($size, -1);
        $iValue = substr($size, 0, -1);
        switch (strtoupper($sSuffix)) {
            case 'P':
                $iValue *= 1024;
            case 'T':
                $iValue *= 1024;
            case 'G':
                $iValue *= 1024;
            case 'M':
                $iValue *= 1024;
            case 'K':
                $iValue *= 1024;
                break;
        }
        return $iValue;
    }

    /**
     * Get download time flag
     *
     * @return string
     */
    public function getDownloadTimeFlag()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $downloadTime = $this->_scopeConfig->getValue('attachment/general/show_download_number', $storeScope);
        return $downloadTime;
    }

    /**
     * Get attachment size
     *
     * @return bool
     */
    public function getAttachmentSizeFlag()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $showSize = $this->_scopeConfig->getValue('attachment/general/show_file_size', $storeScope);
        return $showSize;
    }

    /**
     * Get product tabs download tittle
     *
     * @return string
     */
    public function getDownloadTabTitle()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $title = $this->_scopeConfig->getValue('attachment/general/tab_title', $storeScope);
        return $title;
    }

    /**
     * Get show product tabs
     *
     * @return bool
     */
    public function getShowInProductTabFlag()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $flag = $this->_scopeConfig->getValue('attachment/general/show_product_tab', $storeScope);
        return $flag;
    }

    /**
     * Get show in block
     *
     * @return bool
     */
    public function getShowInBlockFlag()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $flag = $this->_scopeConfig->getValue('attachment/general/show_block', $storeScope);
        return $flag;
    }

    /**
     * Get block download tittle
     *
     * @return string
     */
    public function getBlockAttachmentTitle()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $title = $this->_scopeConfig->getValue('attachment/general/block_title', $storeScope);
        return $title;
    }

    /**
     * Get images base url
     *
     * @return string
     */
    public function getAtttachmentUrl()
    {
        $subDir = 'bss/productattachment/';
        return $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA])
            . $subDir;
    }

    /**
     * Sort Attachment by position
     *
     * @param array $firstAttachments
     * @return array | bool
     */
    public function sortAttachment($firstAttachments)
    {
        $attachments = [];
        foreach ($firstAttachments as $key => $attachment) {

            if (isset($attachment['position'])) {
                $attachments[] = $attachment;
            }
        }
        if (!empty($attachments)) {
            for ($i=0; $i<count($attachments); $i++) {
                $val = $attachments[$i];
                $j = $i-1;
                while ($j>=0 && $attachments[$j]['position'] > $val['position']) {
                    $attachments[$j+1] = $attachments[$j];
                    $j--;
                }
                $attachments[$j+1] = $val;
            }
            return $attachments;
        }
        return false;
    }

    /**
     * Is require file upload
     *
     * @param \Bss\ProductAttachment\Model\File $attachment
     * @return bool
     */
    public function isRequireFileUpload($attachment)
    {
        if (!empty($attachment->getId()) && $attachment->getType()) {
            return false;
        }
        return true;
    }

    /**
     * Get File Name Attachment
     *
     * @param \Bss\ProductAttachment\Model\File $attachment
     * @return string
     */
    public function getFileNameAttachment($attachment)
    {
        if ($attachment->getType() && !empty($attachment->getUploadedFile())) {
            return "Current file: ". $attachment->getUploadedFile()."<br/>";
        }
        return "";
    }

    /**
     * Get products tab Url in admin
     *
     * @return string
     */
    public function getProductsGridUrl()
    {
        return $this->_backendUrl->getUrl('bss_productattachment/file/products', ['_current' => true]);
    }

    /**
     * Get products tab Url in admin
     *
     * @param String $productId
     * @return bool
     */
    public function hasAttachment($productId)
    {
        $product = $this->_product->create()->load($productId);
        $attachmentList = $product->getData('bss_productattachment');
        if (!empty($attachmentList)) {
            return true;
        }
        return false;
    }

    /**
     * Get Customer Group Id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->customerSession->isLoggedIn()) {
            $groupId = $this->customerSession->getCustomer()->getGroupId();
            return $groupId;
        }
        return 0;
    }
}
