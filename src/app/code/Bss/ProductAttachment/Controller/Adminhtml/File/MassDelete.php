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
namespace Bss\ProductAttachment\Controller\Adminhtml\File;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Mass Action Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * @var \Bss\ProductAttachment\Model\ResourceModel\File\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollection;

    /**
     * Product Factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Bss\ProductAttachment\Model\ResourceModel\File\CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Bss\ProductAttachment\Model\ResourceModel\File\CollectionFactory $collectionFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_product = $productFactory;
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_productCollection = $productCollection;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());

        foreach ($collection->getData() as $key => $value) {
            $productSelected = $this->getProducts($value['file_id']);
            $this->ignoreProduct($productSelected, $value['file_id']);
        }

        $delete = 0;
        foreach ($collection as $item) {
            /** @var \Bss\ProductAttachment\Model\File $item */
            $this->removeAttachment($item);
            $delete++;
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $delete));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Remove Attachment
     *
     * @param \Bss\ProductAttachment\Model\File $attachment
     * @return void
     */
    protected function removeAttachment($attachment)
    {
        $attachment->delete();
    }

    /**
     * Remove attachment from bss_productattachment attribute
     *
     * @param array $productList
     * @param String $fileId
     * @return void
     */
    protected function ignoreProduct($productList, $fileId)
    {
        foreach ($productList as $key => $productId) {
            $product = $this->getProductById($productId);
            $attachmentList = $product->getData('bss_productattachment');
            $product->addData(
                ['bss_productattachment' => $this->removeAttachmentFromAttribute($attachmentList, $fileId)
                ]
            );
            $this->saveProduct($product);
        }
    }

    /**
     * Process remove attachment id bss_productattachment value
     *
     * @param String $attribute
     * @param String $fileId
     * @return string
     */
    protected function removeAttachmentFromAttribute($attribute, $fileId)
    {
        $res = "";
        if ($attribute !== null) {
            $attachmentList = explode(",", $attribute);
        } else {
            $attachmentList = [];
        }

        if (count($attachmentList) > 1) {
            $key = array_search($fileId, $attachmentList);
            if ($key !== false) {
                unset($attachmentList[$key]);
            }

            $res = implode(",", $attachmentList);
        }
        return $res;
    }

    /**
     * Get Product by Attachment Id
     *
     * @param String  $fileId
     * @return array
     */
    protected function getProducts($fileId)
    {
        $productSelected = [];

        $productCollection = $this->_productCollection->create();

        $collection = $productCollection->addAttributeToSelect('*')
            ->addAttributeToFilter(
                'bss_productattachment',
                ['finset' => [$fileId]]
            )
            ->load();

        foreach ($collection as $product) {
            array_push($productSelected, $product->getId());
        }
        return $productSelected;
    }

    /**
     * Get Product By Id
     *
     * @param String $id
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProductById($id)
    {
        $product = $this->_product->create()->load($id);
        return $product;
    }

    /**
     * Save to Product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function saveProduct($product)
    {
        $product->save();
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_ProductAttachment::delete");
    }
}
