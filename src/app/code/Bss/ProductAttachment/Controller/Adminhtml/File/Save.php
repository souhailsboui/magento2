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

use Bss\ProductAttachment\Helper\Data;
use Bss\ProductAttachment\Model\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Save extends \Bss\ProductAttachment\Controller\Adminhtml\File
{
    /**
     * @var Session
     */
    protected $_backendSession;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Product Factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductAction
     */
    private $productAction;

    /**
     * @param ProductFactory $productFactory
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @param FileFactory $fileFactory
     * @param Registry $registry
     * @param Context $context
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param ProductAction $productAction
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Bss\ProductAttachment\Model\FileFactory $fileFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context,
        \Bss\ProductAttachment\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductAction $productAction
    ) {
        $this->helper = $helper;
        $this->_product = $productFactory;
        $this->_fileSystem = $filesystem;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_backendSession = $context->getSession();
        $this->_storeManager = $storeManager;
        $this->productAction = $productAction;
        parent::__construct($fileFactory, $registry, $context);
    }

    /**
     * Run the action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('file');
        $data = $this->_filterPostData($data);
        $productIds = $this->filterProductsPostData();
        $storeView = $data['store_view'];

        unset($data['store_view']);
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $file = $this->_initFile();

            $file->setData($data);
            $currentFileName = $file->getData('uploaded_file');
            $currentSize = $file->getData('size');

            try {
                $dataFile = $this->uploadFileAndGetFileName('file', $currentFileName);

                $filename = is_array($dataFile) ? $dataFile['file'] : $dataFile;
                $file->setUploadedFile($filename);

                $size = is_array($dataFile) ? ($dataFile['size']/1024) : $currentSize;
                $file->setSize($size);

                $file->save();

                $productsSelected = $this->_fileFactory->create()->getProducts($file);

                if ($file->getStoreId() !== null) {
                    $storeIds = explode(",", $file->getStoreId());
                } else {
                    $storeIds = [];
                }

                if (in_array(0, $storeIds)) {
                    $allStoreViewIds = [];
                    foreach ($this->_storeManager->getStores() as $store) {
                        $allStoreViewIds[] = $store->getId();
                    }
                    $storeIds = array_unique(array_merge($storeIds, $allStoreViewIds));
                }
                $ignoreProductIds = $this->unassignList($productsSelected, $productIds);
                foreach ($storeIds as $storeId) {
                    $this->ignoreProduct($ignoreProductIds, $file->getId(), $storeId);
                }

                $applyProductIds = $this->newAssignList($productsSelected, $productIds);

                foreach ($storeIds as $storeId) {
                    $this->applyProduct($applyProductIds, $file->getId(), $storeId);
                }

                $this->messageManager->addSuccessMessage(__('The Attachment has been saved.'));
                $this->_backendSession->setBssProductAttachmentFileData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'bss_productattachment/*/edit',
                        [
                            'file_id' => $file->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }

                $resultRedirect->setPath('bss_productattachment/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->_getSession()->setBssProductAttachmentFileData($data);
            $resultRedirect->setPath(
                'bss_productattachment/*/edit',
                [
                    'file_id' => $file->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }

        $resultRedirect->setPath('bss_productattachment/*/');
        return $resultRedirect;
    }

    /**
     * Filter data post
     *
     * @param array $data
     * @return array
     */
    protected function _filterPostData($data)
    {
        if (isset($data['store_id']) && isset($data['customer_group'])) {
            if (is_array($data['store_id'])) {
                $data['store_id'] = implode(',', $data['store_id']);
            }

            if (is_array($data['customer_group'])) {
                $data['customer_group'] = implode(',', $data['customer_group']);
            }

            if (!$data['type']) {
                $data['uploaded_file'] = $data['link_file'];
                unset($data['link_file']);
            }
        }
        return $data;
    }

    /**
     * Upload File and return new file name
     *
     * Return current file name if don't have file upload
     *
     * @param String $uploadField
     * @param String $currentFileName
     * @return array
     * @throws \Exception
     */
    protected function uploadFileAndGetFileName($uploadField, $currentFileName)
    {
        $file = $this->getRequest()->getFiles($uploadField);

        $maxSize = $this->helper->getMaxFileSize();

        if (!empty($file) && $file['error'] == 0) {
            if ($file['size'] > $maxSize) {
                throw new \Exception("Not Allowed! Your file size exceeds configured limit.", 1);
            }

            $mediaDir = $this->_fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

            $target = $mediaDir->getAbsolutePath('/bss/productattachment/');

            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_uploaderFactory->create(['fileId' => $uploadField]);

            /** Allowed extension types */
            $uploader->setAllowedExtensions(
                [
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
                    'ldt',
                    'ies',
                    'dwg',
                ]
            );

            /** Rename file name if already exists */
            $uploader->setAllowRenameFiles(true);

            $uploader->setFilesDispersion(false);

            /** Allow Create Folders */
            $uploader->setAllowCreateFolders(true);

            /** upload file in folder "bss/productattachment/file" */
            $result = $uploader->save($target);

            return $result;
        } else {
            if (filter_var($currentFileName, FILTER_VALIDATE_URL)) {
                $data['file'] = $currentFileName;
                $data['size'] = 0;
            } else {
                $data = $currentFileName;
            }

            return $data;
        }
    }

    /**
     * Save attachment to bss_productattachment attribute
     *
     * @param array $productList
     * @param string $fileId
     * @param string $storeId
     * @return void
     */
    protected function applyProduct($productList, $fileId, $storeId)
    {
        foreach ($productList as $key => $productId) {
            $product = $this->getProductByIdAndStore($productId, $storeId);
            $attachmentList = $product->getData('bss_productattachment');
            $this->productAction->updateAttributes(
                [$productId],
                ['bss_productattachment' => $this->addAttachmentToAttribute($attachmentList, $fileId)],
                $storeId
            );
        }
    }

    /**
     * Remove attachment from bss_productattachment attribute
     *
     * @param array $productList
     * @param string $fileId
     * @param string $storeId
     * @return void
     */
    protected function ignoreProduct($productList, $fileId, $storeId)
    {
        foreach ($productList as $key => $productId) {
            $product = $this->getProductByIdAndStore($productId, $storeId);
            $attachmentList = $product->getData('bss_productattachment');
            $this->productAction->updateAttributes(
                [$productId],
                ['bss_productattachment' => $this->removeAttachmentFromAttribute($attachmentList, $fileId)],
                $storeId
            );
        }
    }

    /**
     * Process add attachment id bss_productattachment value
     *
     * @param String $attribute
     * @param String $fileId
     * @return string
     */
    protected function addAttachmentToAttribute($attribute, $fileId)
    {
        if (!empty($attribute)) {
            $attachmentList = explode(",", $attribute);

            if (!in_array($fileId, $attachmentList)) {
                $attachmentList[] = $fileId;
            }

            $attachmentList = implode(",", $attachmentList);
            return $attachmentList;
        }
        return $fileId;
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
     * Get new product list
     *
     * @param array $oldList
     * @param array $newList
     * @return array
     */
    protected function newAssignList($oldList, $newList)
    {
        $newAssign =[];
        if (null!== $newList) {
            $newAssign = array_diff($newList, $oldList);
        }
        return $newAssign;
    }

    /**
     * Get remove product list
     *
     * @param array $oldList
     * @param array $newList
     * @return array
     */
    protected function unassignList($oldList, $newList)
    {
        $unassigned =[];
        if (null!== $newList) {
            $unassigned = array_diff($oldList, $newList);
        }
        return $unassigned;
    }

    /**
     * Filter Products Post Data
     *
     * @return array|void
     */
    protected function filterProductsPostData()
    {
        if (null !== $this->getRequest()->getPost('products')) {
            $productList = $this->getRequest()->getPost('products');

            $productList = explode("&", $productList);
            foreach ($productList as $key => $value) {
                if (!is_numeric($value)) {
                    array_splice($productList, $key, 1);
                }
            }
            return $productList;
        }
    }

    /**
     * Get Product By Id
     *
     * @param String $id
     * @param string $storeId
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProductByIdAndStore($id, $storeId)
    {
        $product = $this->_product->create()->setStoreId($storeId)->load($id);
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
        return $this->_authorization->isAllowed("Bss_ProductAttachment::save");
    }
}
