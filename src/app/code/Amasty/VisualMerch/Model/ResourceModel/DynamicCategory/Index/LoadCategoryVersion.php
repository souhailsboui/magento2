<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\Index;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\CategoryProcessor;
use Magento\Framework\App\ResourceConnection;

class LoadCategoryVersion
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategoryProcessor
     */
    private $categoryProcessor;

    public function __construct(ResourceConnection $resourceConnection, CategoryProcessor $categoryProcessor)
    {
        $this->resourceConnection = $resourceConnection;
        $this->categoryProcessor = $categoryProcessor;
    }

    public function execute(int $categoryId): int
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $this->resourceConnection->getTableName(
                $this->categoryProcessor->getIndexer()->getView()->getChangelog()->getName()
            )
        )->where(
            'entity_id = ?',
            $categoryId
        )->order(
            'version_id DESC'
        )->limit(1);

        return (int)$this->resourceConnection->getConnection()->fetchOne($select);
    }
}
