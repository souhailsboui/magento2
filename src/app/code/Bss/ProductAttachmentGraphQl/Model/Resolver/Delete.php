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
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Delete implements ResolverInterface
{
    /**
     * @var ProductAttachmentRepositoryInterface
     */
    protected $attachmentRepository;

    /**
     * Constructor
     *
     * @param ProductAttachmentRepositoryInterface $attachmentRepository
     */
    public function __construct(ProductAttachmentRepositoryInterface $attachmentRepository)
    {
        $this->attachmentRepository = $attachmentRepository;
    }

    /**
     * Delete Product Attachment by fileId
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws CouldNotSaveException
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['file_id'])) {
            throw  new GraphQlInputException(__("file_id is not empty"));
        }
        $file_id = $args['file_id'];
        return $this->deleteAttachment($file_id);
    }

    /**
     * Delete
     *
     * @param int $file_id
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function deleteAttachment($file_id)
    {
        $this->attachmentRepository->deleteById($file_id);
        return [
            "status" => " success",
            "message" => "Delete product attachment successfully!"
        ];
    }
}
