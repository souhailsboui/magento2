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
use Bss\ProductAttachment\Model\ProductAttachmentRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetList implements ResolverInterface
{
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
     * @param ProductAttachmentRepository $attachmentRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        ProductAttachmentRepository $attachmentRepository,
        SearchCriteriaBuilder       $criteriaBuilder,
        FilterBuilder               $filterBuilder,
        FilterGroupBuilder          $filterGroupBuilder
    ) {
        $this->attachmentRepository = $attachmentRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Get list product attachment
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $attachments = [];
        try {
            $searchResult = $this->getSearchResult($args);
            foreach ($searchResult as $item) {
                $attachments[] = $item->getData();
            }
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
        return $attachments;
    }

    /**
     * Get search result
     *
     * @param array $args
     * @return ProductAttachmentInterface[]
     */
    public function getSearchResult(array $args)
    {
        $filterGroups = [];
        if (isset($args['filter'])) {
            $filters = [];
            foreach ($args['filter'] as $field => $cond) {
                if (isset($this->fieldTranslatorArray[$field])) {
                    $field = $this->fieldTranslatorArray[$field];
                }
                $searchValue = str_replace('%', '', $cond);
                $filters[] = $this->filterBuilder->setField($field)
                    ->setValue("%{$searchValue}%")
                    ->setConditionType('like')
                    ->create();
            }
            $this->filterGroupBuilder->setFilters($filters);
            $filterGroups[] = $this->filterGroupBuilder->create();
        }
        $this->criteriaBuilder->setFilterGroups($filterGroups);

        if (isset($args['page_size'])) {
            $this->criteriaBuilder->setPageSize($args['page_size']);
        }
        return $this->attachmentRepository->getList($this->criteriaBuilder->create())->getItems();
    }
}
