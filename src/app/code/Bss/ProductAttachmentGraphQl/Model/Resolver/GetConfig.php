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

use Bss\ProductAttachment\Helper\Data;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetConfig implements ResolverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * ProductAttachment constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * Get config
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return $this->configModule();
    }

    /**
     * Config module
     *
     * @return array
     */
    public function configModule()
    {
        return [
            "enable" => $this->helperData->enable(),
            "showProductTab" => $this->helperData->showProductTab(),
            "tabTitle" => $this->helperData->tabTitle(),
            "showBlock" => $this->helperData->showBlock(),
            "blockTitle" => $this->helperData->blockTitle(),
            "maxSize" => $this->helperData->maxZize(),
            "showFileSize" => $this->helperData->showFileSize(),
            "showDownloadNumber" => $this->helperData->showDownloadNumber(),
        ];
    }
}
