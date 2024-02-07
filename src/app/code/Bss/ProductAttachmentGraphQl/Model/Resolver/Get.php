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

use Bss\ProductAttachment\Api\Data\ProductAttachmentInterface;
use Bss\ProductAttachment\Model\File;
use Bss\ProductAttachment\Model\ProductAttachmentRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Get implements ResolverInterface
{
    /**
     * ProductRepository
     *
     * @var ProductAttachmentRepository
     */
    protected $attachmentRepository;

    /**
     * Constructor
     *
     * @param ProductAttachmentRepository $attachmentRepository
     */
    public function __construct(
        ProductAttachmentRepository $attachmentRepository
    ) {
        $this->attachmentRepository = $attachmentRepository;
    }

    /**
     * Get product attachment by id
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
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['file_id'])) {
            throw  new GraphQlInputException(__("file_id is field"));
        }
        $file_id = $args['file_id'];
        return $this->attachmentById($file_id);
    }

    /**
     * Attachment by id
     *
     * @param int $file_id
     * @return ProductAttachmentInterface|File|mixed
     * @throws NoSuchEntityException
     */
    public function attachmentById($file_id)
    {
        return $this->attachmentRepository->getById($file_id);
    }
}
