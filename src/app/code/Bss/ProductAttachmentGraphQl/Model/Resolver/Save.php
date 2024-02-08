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
 * @package    Bss_ProductAttachmentGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductAttachmentGraphQl\Model\Resolver;

use Bss\ProductAttachment\Api\ProductAttachmentRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Save implements ResolverInterface
{
    /**
     * @var ProductAttachmentRepositoryInterface
     */
    protected $productAttachmentRepository;

    /**
     * Create constructor.
     *
     * @param ProductAttachmentRepositoryInterface $productAttachmentRepository
     */
    public function __construct(ProductAttachmentRepositoryInterface $productAttachmentRepository)
    {
        $this->productAttachmentRepository = $productAttachmentRepository;
    }

    /**
     * Save Attachment
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty(['input'])) {
            throw new GraphQlInputException(__('"input" value should'));
        }

        $data = $args ['input'];
        return $this->saveAttachment($data);
    }

    /**
     * Save attachment
     *
     * @param array $data
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function saveAttachment($data)
    {
        $file_id = isset($data['file_id']) ? (int)$data['file_id'] : 0;
        $attachment = $this->productAttachmentRepository->getById($file_id);
        if (isset($data['file_id']) && $data['file_id']) {
            $attachment->setFileId($data['file_id']);
        }
        if (isset($data['title']) && $data['title']) {
            $attachment->setTitle($data['title']);
        }

        if (isset($data['description']) && $data['description']) {
            $attachment->setDescription($data['description']);
        }

        if (isset($data['status']) && $data['status']) {
            $attachment->setStatus($data['status']);
        }

        if (isset($data['type']) && $data['type']) {
            $attachment->setType($data['type']);
        }

        if (isset($data['uploaded_file']) && $data['uploaded_file']) {
            $attachment->setUploadedFile($data['uploaded_file']);
        }

        if (isset($data['size']) && $data['size']) {
            $attachment->setSize($data['size']);
        }

        if (isset($data['store_id']) && $data['store_id']) {
            $attachment->setStoreId($data['store_id']);
        }

        if (isset($data['customer_group']) && $data['customer_group']) {
            $attachment->setCustomerGroup($data['customer_group']);
        }

        if (isset($data['limit_time']) && $data['limit_time']) {
            $attachment->setLimitTime($data['limit_time']);
        }

        if (isset($data['position']) && $data['position']) {
            $attachment->setPosition($data['position']);
        }

        if (isset($data['downloaded_time']) && $data['downloaded_time']) {
            $attachment->setDownloadedTime($data['downloaded_time']);
        }

        if (isset($data['show_footer']) && $data['show_footer']) {
            $attachment->setShowFooter($data['show_footer']);
        }
        $this->productAttachmentRepository->save($attachment);
        return $attachment;
    }
}
