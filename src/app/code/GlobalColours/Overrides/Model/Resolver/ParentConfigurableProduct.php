<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace GlobalColours\Overrides\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Psr\Log\LoggerInterface;


/**
 * @inheritdoc
 */
class ParentConfigurableProduct implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ValueFactory $valueFactory
     * @param MetadataPool $metadataPool
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param LoggerInterface $logger
     */
    public function __construct(
        ValueFactory $valueFactory,
        MetadataPool $metadataPool,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        LoggerInterface $logger,
    ) {
        $this->valueFactory = $valueFactory;
        $this->metadataPool = $metadataPool;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $identifierField = $this->metadataPool->getMetadata(ProductInterface::class)->getIdentifierField();

        if (!isset($value[$identifierField])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        $result = function () use ($value, $identifierField, $context) {

            // Get parent ids
            $parentIds = $this->configurable->getParentIdsByChild($value[$identifierField]);

            if (!$parentIds || !$parentIds[0]) {
                return null;
            }

            $parentId = $parentIds[0];

            // Get product by id from the first parent configurable product
            $parentProduct = $this->productRepository->getById($parentId);

            if (!$parentProduct || !$parentProduct->getId()) {
                return null;
            }

            $data = ['model' => $value['model']];

            return array_replace($parentProduct->getData(), $data);
        };

        return $this->valueFactory->create($result);
    }
}
