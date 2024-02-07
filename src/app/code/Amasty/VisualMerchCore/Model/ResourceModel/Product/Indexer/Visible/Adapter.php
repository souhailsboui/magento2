<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\ResourceModel\Product\Indexer\Visible;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\AbstractIndexer;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

class Adapter extends AbstractIndexer
{
    private const TABLE_NAME = 'amasty_merchandiser_visible_product';
    public const ID_FIELD_NAME = 'product_id';
    public const STORE_ID_FIELD_NAME = 'store_id';

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        StrategyInterface $tableStrategy,
        EavConfig $eavConfig,
        ProductCollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $tableStrategy, $eavConfig, $connectionName);

        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Initialize connection and define main index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }

    /**
     * Rebuild index data by entities
     *
     * @param array $processIds
     */
    public function reindexEntities(array $processIds = []): void
    {
        $this->clearTemporaryIndexTable();
        $this->prepareIndex($processIds);
    }

    private function prepareIndex(array $entityIds = []): void
    {
        $connection = $this->getConnection();
        $idxTable = $this->getIdxTable();

        foreach ($this->storeManager->getStores() as $store) {
            /** @var ProductCollection $productCollection */
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->setStoreId($store->getId());
            $productCollection->addAttributeToFilter(
                ProductInterface::STATUS,
                ProductStatus::STATUS_ENABLED
            );
            $productCollection->addAttributeToFilter(
                ProductInterface::VISIBILITY,
                [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]
            );

            if (!empty($entityIds)) {
                $productCollection->addIdFilter($entityIds);
            }

            $select = $productCollection->getSelect();
            $select->reset(Select::COLUMNS);
            $select->columns([
                self::ID_FIELD_NAME => $productCollection->getEntity()->getIdFieldName(),
                self::STORE_ID_FIELD_NAME => new \Zend_Db_Expr($store->getId())
            ]);

            $query = $select->insertIgnoreFromSelect($idxTable);
            $connection->query($query);
        }
    }

    /**
     * @param null $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        return $this->tableStrategy->getTableName(self::TABLE_NAME);
    }
}
