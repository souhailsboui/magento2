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
namespace Bss\ProductAttachment\Model;

use \Magento\Framework\Model\AbstractModel;
use \Bss\ProductAttachment\Api\Data\ProductAttachmentInterface;

class File extends AbstractModel implements ProductAttachmentInterface
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'bss_productattachment_file';

    /**
     *
     * @var string
     */
    protected $_cacheTag = 'bss_productattachment_file';

    /**
     *
     * @var string
     */
    protected $_eventPrefix = 'bss_productattachment_file';

    /**
     * Product Colection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollection;

    /**
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $data = []
    ) {
        $this->_request = $request;
        $this->_productCollection = $collectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bss\ProductAttachment\Model\ResourceModel\File');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }

    /**
     * Get products
     *
     * @param \Bss\ProductAttachment\Model\File $attachment
     * @return array
     */
    public function getProducts($attachment)
    {
        $productSelected = [];
        $productCollection = $this->_productCollection->create();

        $collection = $productCollection->addAttributeToSelect('bss_productattachment')->load();

        foreach ($collection as $product) {
            if ($product->getData('bss_productattachment')) {
                $bssProductAttachment = $product->getData('bss_productattachment');
                if ($bssProductAttachment !== null) {
                    $attachments = explode(',', $bssProductAttachment);
                } else {
                    $attachments = [];
                }

                if (in_array($attachment->getId(), $attachments)) {
                    array_push($productSelected, $product->getId());
                }
            }
        }
        return array_unique($productSelected);
    }

    /**
     * @inheritdoc
     */
    public function getFileId()
    {
        return $this->getData(self::FILE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function getUploadedFile()
    {
        return $this->getData(self::UPLOADED_FILE);
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return $this->getData(self::SIZE);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroup()
    {
        return $this->getData(self::CUSTOMER_GROUP);
    }

    /**
     * @inheritdoc
     */
    public function getLimitTime()
    {
        return $this->getData(self::LIMIT_TIME);
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function getDownloadedTime()
    {
        return $this->getData(self::DOWNLOADED_TIME);
    }

    /**
     * @inheritdoc
     */
    public function getShowFooter()
    {
        return $this->getData(self::SHOW_FOOTER);
    }

    /**
     * @inheritdoc
     */
    public function setFileId($file_id)
    {
        return $this->setData(self::FILE_ID, $file_id);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function setUploadedFile($uploaded_file)
    {
        return $this->setData(self::UPLOADED_FILE, $uploaded_file);
    }

    /**
     * @inheritdoc
     */
    public function setSize($size)
    {
        return $this->setData(self::SIZE, $size);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($store_id)
    {
        return $this->setData(self::STORE_ID, $store_id);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroup($customer_group)
    {
        return $this->setData(self::CUSTOMER_GROUP, $customer_group);
    }

    /**
     * @inheritdoc
     */
    public function setLimitTime($limit_time)
    {
        return $this->setData(self::LIMIT_TIME, $limit_time);
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritdoc
     */
    public function setDownloadedTime($downloaded_time)
    {
        return $this->setData(self::DOWNLOADED_TIME, $downloaded_time);
    }

    /**
     * @inheritdoc
     */
    public function setShowFooter($show_footer)
    {
        return $this->setData(self::SHOW_FOOTER, $show_footer);
    }
}
