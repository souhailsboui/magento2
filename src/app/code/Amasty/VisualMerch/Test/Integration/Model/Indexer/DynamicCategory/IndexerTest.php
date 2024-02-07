<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Test\Integration\Model\Indexer\DynamicCategory;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Action\ExecutePartial;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Indexer;
use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\CategoryIndex;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceProcessor;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IndexerTest extends TestCase
{
    /**
     * @dataProvider executeDataProvider
     * @magentoDataFixture Amasty_VisualMerch::Test/Integration/_files/dynamic_categories.php
     * @magentoDataFixture Amasty_VisualMerch::Test/Integration/_files/products.php
     *
     * @covers \Amasty\VisualMerch\Model\Indexer\DynamicCategory\Indexer::execute
     *
     * @param string $sku
     * @param int[] $categoryIds
     * @param int $storeId
     */
    public function testExecute(string $sku, array $categoryIds, int $storeId): void
    {
        /** @var ProductPriceProcessor $productPriceProcessor */
        $productPriceProcessor = Bootstrap::getObjectManager()->get(ProductPriceProcessor::class);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        /** @var CategoryIndex $categoryIndex */
        $categoryIndex = Bootstrap::getObjectManager()->get(CategoryIndex::class);

        /** @var ExecutePartial $executePartial */
        $executePartial = Bootstrap::getObjectManager()->create(ExecutePartial::class, [
            'indexerType' => Indexer::PRODUCT_INDEXER_TYPE
        ]);

        /** @var Indexer $indexer */
        $indexer = Bootstrap::getObjectManager()->create(Indexer::class, [
            'executePartial' => $executePartial
        ]);

        $productId = (int) $productRepository->get($sku)->getId();
        $productPriceProcessor->reindexRow($productId);
        $indexer->execute([$productId]);

        $this->assertEquals($categoryIds, array_map(function ($categoryId) {
            return (int) $categoryId;
        }, $categoryIndex->loadCategoryIds($productId, $storeId)));
    }

    public function executeDataProvider(): array
    {
        return [
            ['merch-simple-1', [9392], 1],
            ['merch-simple-2', [9391, 9392], 1],
            ['merch-simple-3', [9391], 1],
            ['merch-simple-4', [9391], 1]
        ];
    }
}
