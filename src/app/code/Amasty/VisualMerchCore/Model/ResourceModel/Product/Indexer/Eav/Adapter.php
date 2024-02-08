<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\DB\Select;
use Zend_Db_Expr;

/**
 * @codeCoverageIgnore
 */
class Adapter extends AbstractEav
{
    public const TRANSIT_PREFIX = 'transit_';

    /**
     * Initialize connection and define main index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_merchandiser_product_index_eav', 'entity_id');
    }

    /**
     * @param null $entityIds
     * @param null $attributeId
     * @return $this|AbstractEav
     * @throws \Zend_Db_Statement_Exception
     */
    protected function _prepareIndex($entityIds = null, $attributeId = null)
    {
        $attributeId = $attributeId === null ? $attributeId : (int)$attributeId;
        $this->prepareSelectIndex('varchar', $entityIds, $attributeId);
        $this->prepareSelectIndex('int', $entityIds, $attributeId);
        $this->prepareMultiselectIndex('varchar', $entityIds, $attributeId);
        $this->prepareMultiselectIndex('text', $entityIds, $attributeId);

        return $this;
    }

    /**
     * @param bool $multiSelect
     * @param string|null $type
     * @return array
     */
    private function getIndexableAttributes(bool $multiSelect, ?string $type = null): array
    {
        $result = [];
        if ($type !== null) {
            $select = $this->getConnection()->select()->from(
                ['ca' => $this->getTable('catalog_eav_attribute')],
                'attribute_id'
            )->join(
                ['ea' => $this->getTable('eav_attribute')],
                'ca.attribute_id = ea.attribute_id',
                []
            )->where(
                'ea.backend_type = ?',
                $type
            )->where('ca.is_used_for_promo_rules > 0');

            if ($multiSelect == true) {
                $select->where('ea.frontend_input = ?', 'multiselect');
            } else {
                $select->where('ea.frontend_input = ?', 'select');
            }

            $result = $this->getConnection()->fetchCol($select);
        }

        return $result;
    }

    /**
     * @param string $type
     * @param array|null $entityIds
     * @param int|null $attributeId
     * @return Adapter
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function prepareSelectIndex(string $type, ?array $entityIds = null, ?int $attributeId = null): Adapter
    {
        $connection = $this->getConnection();
        $idxTable = $this->getIdxTable();
        // prepare select attributes
        $attrIds = $attributeId === null ? $this->getIndexableAttributes(false, $type) : [$attributeId];
        if (!$attrIds) {
            return $this;
        }
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $attrIdsFlat = implode(',', array_map('intval', $attrIds));
        $ifNullSql = $connection->getIfNullSql('pis.value', 'COALESCE(ds.value, dd.value)');

        $valueTable = $this->getTable('catalog_product_entity_' . $type);
        /**@var $select \Magento\Framework\DB\Select*/
        $select = $connection->select()->distinct(true)->from(
            ['s' => $this->getTable('store')],
            []
        )->joinLeft(
            ['dd' => $valueTable],
            'dd.store_id = 0',
            []
        )->joinLeft(
            ['ds' => $this->getTable('catalog_product_entity_int')],
            "ds.store_id = s.store_id AND ds.attribute_id = dd.attribute_id AND " .
            "ds.{$productIdField} = dd.{$productIdField}",
            []
        )->joinLeft(
            ['d2d' => $this->getTable('catalog_product_entity_int')],
            sprintf(
                "d2d.store_id = 0 AND d2d.{$productIdField} = dd.{$productIdField} AND d2d.attribute_id = %s",
                $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status')->getId()
            ),
            []
        )->joinLeft(
            ['d2s' => $this->getTable('catalog_product_entity_int')],
            "d2s.store_id = s.store_id AND d2s.attribute_id = d2d.attribute_id AND " .
            "d2s.{$productIdField} = d2d.{$productIdField}",
            []
        )->joinLeft(
            ['cpe' => $this->getTable('catalog_product_entity')],
            "cpe.{$productIdField} = dd.{$productIdField}",
            []
        )->joinLeft(
            ['pis' => $valueTable],
            "pis.{$productIdField} = cpe.{$productIdField} " .
            "AND pis.attribute_id = dd.attribute_id AND pis.store_id = s.store_id",
            []
        )->where(
            's.store_id != 0'
        )->where(
            '(ds.value IS NOT NULL OR dd.value IS NOT NULL)'
        )->where(
            (new Zend_Db_Expr('COALESCE(d2s.value, d2d.value)')) . ' = ' . ProductStatus::STATUS_ENABLED
        )->where(
            "dd.attribute_id IN({$attrIdsFlat})"
        )->where(
            'NOT(pis.value IS NULL AND pis.value_id IS NOT NULL)'
        )->where(
            $ifNullSql . ' IS NOT NULL'
        )->columns(
            [
                'cpe.entity_id',
                'dd.attribute_id',
                's.store_id',
                'value' => new Zend_Db_Expr('COALESCE(ds.value, dd.value)')
            ]
        );

        if ($entityIds !== null) {
            $ids = implode(',', array_map('intval', $entityIds));
            $select->where("cpe.entity_id IN({$ids})");
        }

        $query = $select->insertIgnoreFromSelect($idxTable);
        $connection->query($query);
        return $this;
    }

    /**
     * @param string $type
     * @param null $entityIds
     * @param null $attributeId
     * @return $this
     * @throws \Zend_Db_Statement_Exception
     */
    private function prepareMultiselectIndex(string $type, $entityIds = null, $attributeId = null): Adapter
    {
        $connection = $this->getConnection();

        // prepare multiselect attributes
        $attrIds = $attributeId === null ? $this->getIndexableAttributes(true, $type) : [$attributeId];

        if (!$attrIds) {
            return $this;
        }
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();

        // load attribute options
        $select = $connection->select()->from(
            $this->getTable('eav_attribute_option'),
            ['attribute_id', 'option_id']
        )->where('attribute_id IN(?)', $attrIds);

        $valueTable = $this->getTable('catalog_product_entity_' . $type);
        // prepare get multiselect values query
        $productValueExpression = $connection->getCheckSql('pvs.value_id > 0', 'pvs.value', 'pvd.value');
        $select = $connection->select()->from(
            ['pvd' => $valueTable],
            []
        )->join(
            ['cs' => $this->getTable('store')],
            '',
            []
        )->joinLeft(
            ['pvs' => $valueTable],
            "pvs.{$productIdField} = pvd.{$productIdField} AND pvs.attribute_id = pvd.attribute_id"
            . ' AND pvs.store_id=cs.store_id',
            []
        )->joinLeft(
            ['cpe' => $this->getTable('catalog_product_entity')],
            "cpe.{$productIdField} = pvd.{$productIdField}",
            []
        )->where(
            'pvd.store_id=?',
            $connection->getIfNullSql('pvs.store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
        )->where(
            'cs.store_id!=?',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        )->where(
            'pvd.attribute_id IN(?)',
            $attrIds
        )->where(
            'cpe.entity_id IS NOT NULL'
        )->columns(
            [
                'entity_id' => 'cpe.entity_id',
                'attribute_id' => 'attribute_id',
                'store_id' => 'cs.store_id',
                'value' => $productValueExpression
            ]
        );

        $statusCond = $connection->quoteInto('=?', ProductStatus::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', "pvd.{$productIdField}", 'cs.store_id', $statusCond);

        if ($entityIds !== null) {
            $select->where('cpe.entity_id IN(?)', $entityIds);
        }

        $this->saveDataFromSelect($select);

        return $this;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param array $options
     * @throws \Zend_Db_Statement_Exception
     */
    private function saveDataFromSelect(\Magento\Framework\DB\Select $select): void
    {
        $i = 0;
        $data = [];
        $query = $select->query();

        while ($row = $query->fetch()) {
            $data[] = $row;
            $i++;

            if ($i % 10000 == 0) {
                $this->saveIndexData($data);
                $data = [];
            }
        }

        $this->saveIndexData($data);
    }

    /**
     * @param null $parentIds
     * @return $this|AbstractEav
     * @throws \Exception
     */
    protected function _prepareRelationIndex($parentIds = null)
    {
        $connection = $this->getConnection();
        $idxTable = $this->getIdxTable();

        if (!$this->tableStrategy->getUseIdxTable()) {
            $additionalIdxTable = $connection->getTableName(self::TRANSIT_PREFIX . $this->getIdxTable());
            $connection->createTemporaryTableLike($additionalIdxTable, $idxTable);

            $query = $connection->insertFromSelect(
                $this->prepareRelationIndexSelect($parentIds),
                $additionalIdxTable,
                []
            );
            $connection->query($query);

            $select = $connection->select()->from(
                ['additional' => $additionalIdxTable],
                ['entity_id', 'attribute_id', 'store_id', $connection->getConcatSql([
                    'additional.value',
                    'idx.value'
                ], ',')]
            )->joinLeft(
                ['idx' => $idxTable],
                'additional.entity_id = idx.entity_id
                AND additional.attribute_id = idx.attribute_id
                AND additional.store_id = idx.store_id',
                []
            );
            $query = $connection->insertFromSelect(
                $select,
                $idxTable,
                [],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $connection->query($query);

            $connection->dropTemporaryTable($additionalIdxTable);
        } else {
            $query = $connection->insertFromSelect(
                $this->prepareRelationIndexSelect($parentIds),
                $idxTable,
                [],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
            );
            $connection->query($query);
        }
        return $this;
    }

    /**
     * @param null $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        return $this->tableStrategy->getTableName('amasty_merchandiser_product_index_eav');
    }

    /**
     * @param array|null $parentIds
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     */
    private function prepareRelationIndexSelect(array $parentIds = null): Select
    {
        $connection = $this->getConnection();
        $table = $this->getIdxTable();
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select()->from(
            ['l' => $this->getTable('catalog_product_relation')],
            []
        )->joinLeft(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.' . $linkField .' = l.parent_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            '',
            []
        )->join(
            ['i' => $table],
            'l.child_id = i.entity_id AND cs.store_id = i.store_id',
            []
        )->group(
            ['parent_id', 'i.attribute_id', 'i.store_id']
        )->columns(
            [
                'parent_id' => 'e.entity_id',
                'attribute_id' => 'i.attribute_id',
                'store_id' => 'i.store_id',
                'value' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT i.value)')
            ]
        );
        if ($parentIds !== null) {
            $ids = implode(',', array_map('intval', $parentIds));
            $select->where("e.entity_id IN({$ids})");
        }

        return $select;
    }

    /**
     * @param array $data
     * @return $this
     */
    private function saveIndexData(array $data)
    {
        if (!$data) {
            return $this;
        }

        $connection = $this->getConnection();
        $table = $this->getIdxTable();
        $columns = array_keys($connection->describeTable($table));
        $connection->insertArray(
            $table,
            $columns,
            $data
        );
        return $this;
    }
}
