<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductAttachmentGraphQl
 * @author    Extension Team
 * @copyright Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttachmentGraphQl\Model\Resolver;

use Bss\ProductAttachment\Api\Data\ProductAttachmentInterface;
use Bss\ProductAttachment\Model\File;
use Bss\ProductAttachment\Model\ProductAttachmentRepository;
use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class DownloadTime implements ResolverInterface
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
     * Get download time attachment
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return File
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['file_id'])) {
            throw new GraphQlInputException(__("File_id is field"));
        }
        $file_id = $args['file_id'];
        return $this->downloadTimeAttachment($file_id);
    }

    /**
     * Download time
     *
     * @param int $file_id
     * @return ProductAttachmentInterface|File|mixed
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function downloadTimeAttachment($file_id)
    {
        $attachment = $this->attachmentRepository->getById($file_id);
        $downloadTime = $attachment->getData('downloaded_time');
        $downloadTime ++;
        $limitDownload = $attachment->getData('limit_time');
        if ($limitDownload-$downloadTime < 0 && $limitDownload!= 0) {
            throw new GraphQlInputException(__("Attachment can't download"));
        } else {
            $attachment->addData(['downloaded_time'=>$downloadTime]);
            $this->attachmentRepository->save($attachment);
            return $attachment;
        }
    }
}
