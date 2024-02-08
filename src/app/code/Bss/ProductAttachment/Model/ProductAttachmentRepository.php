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
 * Product Attachment Repository
 */
class ProductAttachmentRepository implements \Bss\ProductAttachment\Api\ProductAttachmentRepositoryInterface
{
    /**
     * @var \Bss\ProductAttachment\Model\ResourceModel\File\CollectionFactory
     */
    protected $attachmentCollection;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var \Bss\ProductAttachment\Model\ResourceModel\File
     */
    protected $resource;

    /**
     * @var \Bss\ProductAttachment\Model\FileFactory
     */
    protected $attachmentFactory;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessor $collectionProcessor
     * @param \Bss\ProductAttachment\Model\ResourceModel\File\CollectionFactory $attachmentCollection
     * @param \Bss\ProductAttachment\Model\ResourceModel\File $resource
     * @param \Bss\ProductAttachment\Model\FileFactory $attachmentFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder                      $criteriaBuilder,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessor         $collectionProcessor,
        \Bss\ProductAttachment\Model\ResourceModel\File\CollectionFactory $attachmentCollection,
        \Bss\ProductAttachment\Model\ResourceModel\File                   $resource,
        \Bss\ProductAttachment\Model\FileFactory                          $attachmentFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory              $searchResultsFactory
    ) {
        $this->criteriaBuilder = $criteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->attachmentCollection = $attachmentCollection;
        $this->resource = $resource;
        $this->attachmentFactory = $attachmentFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById($file_id)
    {
        if (!isset($this->instances[$file_id])) {
            $pAttachment = $this->attachmentFactory->create();
            $this->resource->load($pAttachment, $file_id);
            if (!$pAttachment->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Product Attachment id "%1" does not exist.', $file_id)
                );
            }
            $this->instances[$file_id] = $pAttachment;
        }
        return $this->instances[$file_id];
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->attachmentCollection->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function save(\Bss\ProductAttachment\Api\Data\ProductAttachmentInterface $productAttachment)
    {
        $file_id = $productAttachment['file_id'];
        try {
            if (isset($file_id)) {
                $this->getById($file_id);
            }
            $this->resource->save($productAttachment);
            $result["status"] = [
                "success" => true,
                "message" => __("You saved.")
            ];
        } catch (\Exception $exception) {
            $result["status"] = [
                "success" => false,
                "message" => __($exception->getMessage())
            ];
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($file_id)
    {
        try {
            $attachment = $this->getById($file_id);
            $this->resource->delete($attachment);
            $result["status"] = [
                "success" => true,
                "message" => __("You deleted.")
            ];
        } catch (\Exception $exception) {
            $result["status"] = [
                "success" => false,
                "message" => __($exception->getMessage())
            ];
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function downloadTime($file_id)
    {
        try {
            $attachment = $this->getById($file_id);
            $downloadTime = $attachment->getData('downloaded_time');
            $downloadTime++;
            $limitDownload = $attachment->getData('limit_time');
            if ($limitDownload - $downloadTime < 0 && $limitDownload != 0) {
                $result["status"] = [
                    "success" => false,
                    "message" => "Attachment can't download"
                ];
                return $result;
            } else {
                $attachment->addData(['downloaded_time' => $downloadTime]);
                $this->resource->save($attachment);
                return $attachment;
            }
        } catch (\Exception $exception) {
            $result["status"] = [
                "success" => false,
                "message" => __($exception->getMessage())
            ];
            return $result;
        }
    }
}
