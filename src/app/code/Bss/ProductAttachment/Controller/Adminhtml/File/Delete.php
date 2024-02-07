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

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Delete extends \Bss\ProductAttachment\Controller\Adminhtml\File
{

    /**
     * @var CollectionFactory
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
     * @param \Bss\ProductAttachment\Model\FileFactory $fileFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @internal param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        \Bss\ProductAttachment\Model\FileFactory $fileFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
    ) {
        $this->_product = $productFactory;
        $this->_productCollection = $productCollection;
        parent::__construct($fileFactory, $coreRegistry, $context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->_resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('file_id');
        if ($id) {
            $title = "";
            try {
                /** @var \Bss\ProductAttachment\Model\File $attachment */
                $attachment = $this->_fileFactory->create();
                $attachment->load($id);
                $title = $attachment->getTitle();
                $fileId = $attachment->getId();

                $productSelected = $this->getProducts($fileId);
                $this->ignoreProduct($productSelected, $fileId);

                $attachment->delete();
                $this->messageManager->addSuccessMessage(__('The Attachment has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_bss_productattachment_file_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                $resultRedirect->setPath('bss_productattachment/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_bss_productattachment_file_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );

                $this->messageManager->addErrorMessage($e->getMessage());

                $resultRedirect->setPath('bss_productattachment/*/edit', ['file_id' => $id]);
                return $resultRedirect;
            }
        }

        $this->messageManager->addErrorMessage(__('Attachment to delete was not found.'));

        $resultRedirect->setPath('bss_productattachment/*/');
        return $resultRedirect;
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
