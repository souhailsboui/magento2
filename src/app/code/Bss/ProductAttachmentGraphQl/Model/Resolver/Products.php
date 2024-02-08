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

use Bss\ProductAttachment\Helper\Data;
use Bss\ProductAttachment\Model\ProductAttachmentRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Products implements ResolverInterface
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * ProductRepository
     *
     * @var ProductAttachmentRepository
     */
    protected $attachmentRepository;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * Constructor
     *
     * @param ProductRepository $productRepository
     * @param Data $helper
     * @param ProductAttachmentRepository $attachmentRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @internal param \Bss\ProductAttachment\Model\FileFactory $fileFactory
     */
    public function __construct(
        ProductRepository           $productRepository,
        Data                        $helper,
        ProductAttachmentRepository $attachmentRepository,
        SearchCriteriaBuilder       $criteriaBuilder,
        FilterBuilder               $filterBuilder,
        FilterGroupBuilder          $filterGroupBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->attachmentRepository = $attachmentRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Get attachment by product id
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        $customerGroupId = $context->getExtensionAttributes()->getCustomerGroupId();
        /* @var $product Product */
        $product = $value['model'];
        return $this->listAttachment($product, $customerGroupId);
    }

    /**
     * Attachment
     *
     * @param Product $product
     * @param int $customerGroupId
     * @return array
     * @throws NoSuchEntityException
     */
    public function listAttachment($product, $customerGroupId)
    {
        $filterGroups = [];
        $this->filterGroupBuilder->setFilters(
            [$this->filterBuilder->setField('customer_group')
                ->setValue($customerGroupId)->setConditionType('finset')->create()]
        );
        $filterGroups[] = $this->filterGroupBuilder->create();

        $product = $this->productRepository->getById($product['entity_id']);
        $pAttachment = $product->getData('bss_productattachment');
        if ($pAttachment !== null) {
            $pAttachment = explode(",", $pAttachment);
        } else {
            $pAttachment = [];
        }
        $pAttachment = array_filter($pAttachment);
        $filters = [];
        foreach ($pAttachment as $value) {
            $searchValue = str_replace('%', '', $value);
            $filters[] = $this->filterBuilder->setField("file_id")
                ->setValue($searchValue)
                ->setConditionType('finset')
                ->create();
        }
        $this->filterGroupBuilder->setFilters($filters);
        $filterGroups[] = $this->filterGroupBuilder->create();
        $this->criteriaBuilder->setFilterGroups($filterGroups);
        return $this->attachmentRepository->getList($this->criteriaBuilder->create())->getItems();
    }
}
