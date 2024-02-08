<?php

declare(strict_types = 1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Locale\FormatInterface;
use Magento\Rule\Model\Condition\Context;
use Magento\Store\Model\StoreManagerInterface;

class Product extends AbstractCondition
{
    public const LIMIT = 1;
    public const TMP_FIELD = 'tmp_xlanding_attribute';
    public const OPERATOR_IS_NOT_ONE_OF = '!()';

    /**
     * @var CategoryCollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $_entityAttributeSetCollectionFactory;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var string
     */
    private $productIdLink;

    /**
     * @var array
     */
    private $staticCategoryIds;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $operatorsMapForEavIndex = [
        '==' => '{}',
        '!=' => '!{}'
    ];

    public function __construct(
        Context $context,
        Data $backendData,
        Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ProductResource $productResource,
        Collection $attrSetCollection,
        FormatInterface $localeFormat,
        CategoryCollectionFactory $categoryCollectionFactory,
        CollectionFactory $entityAttributeSetCollectionFactory,
        Config $eavConfig,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );

        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_entityAttributeSetCollectionFactory = $entityAttributeSetCollectionFactory;
        $this->_eavConfig = $eavConfig;
        $this->productIdLink = $metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $this->storeManager = $storeManager;
    }

    /**
     * Temporary remove negative operators for category condition.
     * Negative validation doesn't work in MySQL muli row without aggregation.
     * Attributes aggregated to index table, but category is not.
     *
     * @return array|null
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType['category'] = ['==', '{}', '()'];
        }
        return $this->_defaultOperatorInputByType;
    }

    public function getAttributeName()
    {
        if ($this->getAttribute()==='attribute_set_id') {
            return __('Attribute Set');
        }

        return $this->getAttributeObject()->getFrontendLabel();
    }

    protected function _prepareValueOptions()
    {
        if ($this->getAttribute() === 'attribute_set_id') {
            $entityTypeId = $this->_eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();

            $selectOptions = $this->_entityAttributeSetCollectionFactory->create()
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();

            $this->setData('value_select_options', $selectOptions);
        }

        return parent::_prepareValueOptions();
    }

    public function getInputType()
    {
        if ($this->getAttribute()==='attribute_set_id') {
            return 'select';
        }

        return parent::getInputType();
    }

    public function getValueElementType()
    {
        if ($this->getAttribute()==='attribute_set_id') {
            return 'select';
        }

        return parent::getValueElementType();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getStaticCategoryIds(): array
    {
        if (!$this->staticCategoryIds) {
            $categories = $this->_categoryCollectionFactory->create();
            $categories->addAttributeToFilter('amlanding_is_dynamic', true);
            $this->staticCategoryIds = $categories->getAllIds();
        }

        return $this->staticCategoryIds;
    }

    protected function excludeDynamicCategories(array $ids)
    {
        return array_diff($ids, $this->getStaticCategoryIds());
    }

    private function includeDynamicCategories(array $ids): array
    {
        return array_merge($ids, $this->getStaticCategoryIds());
    }

    public function getFilterValue()
    {
        $value = parent::getValue();

        if ($this->getAttributeObject()->getAttributeCode() == 'category_ids') {
            if (!is_array($value)) {
                $value = array_map('trim', explode(',', $value));
            }

            $value = $this->getOperator() === self::OPERATOR_IS_NOT_ONE_OF
                ? $this->includeDynamicCategories($value)
                : $this->excludeDynamicCategories($value);
        }

        return $value;
    }

    protected function _getMappingData()
    {
        [$alias, $table, $joinConditions] = $this->getPreparedMappedDataParams();

        return [$alias, $table, $joinConditions];
    }

    public function getStoreManager(): StoreManagerInterface
    {
        return $this->storeManager;
    }

    /**
     * @return array
     */
    private function getPreparedMappedDataParams()
    {
        $joinConditions = '';
        $valueField = 'value';
        $storeId = 0;
        $alias = $this->_getAlias();
        $fieldToTableMap = $this->_getFieldToTableMap($alias);
        if ($fieldToTableMap) {
            [$table, $joinConditions, $valueField] = $fieldToTableMap;
        } else {
            if ($this->isSelectFromIndexTable()) {
                $storeId = $this->getStoreManager()->getStore()->getId();
                $table = $this->_productResource->getTable('amasty_merchandiser_product_index_eav');
                $this->productIdLink = 'entity_id'; //there is no row_id column in this table
            } elseif ($this->getAttributeObject()->isStatic()) {
                $table = null;
                $joinConditions = null;
                $valueField = $this->getAttributeObject()->getAttributeCode();
            } else {
                $storeId = $this->getStoreManager()->getStore()->getId();
                if ($this->getAttributeObject()->getScope() === EavAttributeInterface::SCOPE_GLOBAL_TEXT) {
                    $storeId = 0;
                }

                $table = $this->getAttributeObject()->getBackendTable();
            }
        }

        if (!$joinConditions && !$this->getAttributeObject()->isStatic()) {
            $attributeId = $this->getAttributeObject()->getId();
            $joinPattern = $this->getJoinConditionsPattern()
                . ' AND %1$s.attribute_id = %2$d AND %1$s.store_id = %3$d';
            $joinConditions = sprintf($joinPattern, $alias, $attributeId, $storeId);

            if ($storeId && !$this->isSelectFromIndexTable()) {
                $defaultAlias = 'tad_' . $alias;
                $alias = [$defaultAlias, $alias];
                $storeId = [0, $storeId];
                $joinConditions = [$joinConditions];
                array_unshift(
                    $joinConditions,
                    sprintf($joinPattern, $alias[0], $attributeId, $storeId[0])
                );
            }
        }

        $this->resolveCondition($alias, $valueField);

        return [$alias, $table, $joinConditions, $valueField, $storeId];
    }

    /**
     * @param string|array $alias
     * @param string $valueField
     */
    private function resolveCondition($alias, $valueField): void
    {
        $value = $this->getFilterValue();
        $operator  = $this->getOperatorForValidate();

        if ($this->isSelectFromIndexTable()) {
            $operator = $this->operatorsMapForEavIndex[$operator] ?? $operator;
            $value = is_array($value) ? $value : [$value];
        }

        $this->_condition =  $this->getConditionText($alias, $valueField, $operator, $value);
    }

    /**
     * @param array|$alias
     * @param string $valueField
     * @param string $operator
     * @param mixed $value
     * @return string
     */
    private function getConditionText($alias, $valueField, $operator, $value): string
    {
        if (!is_array($alias)) {
            $condition = $this->_getCondition($alias, $valueField, $operator, $value);
        } else {
            $eavColumn = "IFNULL(`{$alias[1]}`.`$valueField`, `{$alias[0]}`.`$valueField`)";
            $condition = $this->getOperatorCondition(self::TMP_FIELD, $operator, $value);
            $count = self::LIMIT;
            $condition = str_replace('`' . self::TMP_FIELD . '`', $eavColumn, $condition, $count);
        }

        return $condition;
    }

    /**
     * @return bool
     */
    private function isSelectFromIndexTable()
    {
        return in_array($this->getAttributeObject()->getFrontendInput(), ['select', 'multiselect'], true);
    }

    private function getJoinConditionsPattern(): string
    {
        return sprintf('e.%1$s = %2$s.%1$s', $this->productIdLink, '%1$s');
    }

    protected function _getCondition($alias, $valueField, $operator, $value)
    {
        return $this->getOperatorCondition("{$alias}.{$valueField}", $operator, $value);
    }

    /**
     * @param string $alias
     * @return array|null array('table name', 'join condition', 'value column name')
     */
    protected function _getFieldToTableMap($alias): ?array
    {
        switch ($this->getAttributeObject()->getAttributeCode()) {
            case 'price':
                return [
                    $this->_productResource->getTable('catalog_product_index_price'),
                    $this->_productResource->getConnection()->quoteInto(
                        'e.entity_id = ' . $alias . '.entity_id AND ' . $alias . '.website_id = ?',
                        $this->getStoreManager()->getStore()->getWebsiteId()
                    ),
                    'price'
                ];
            case 'category_ids':
                return [
                    $this->_productResource->getTable('catalog_category_product'),
                    'e.entity_id = ' . $alias . '.product_id',
                    'category_id'
                ];
        }

        return null;
    }

    /**
     * @param ProductCollection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        $select = $productCollection->getSelect();
        if ($this->getAttributeObject()->getAttributeId() || $this->getAttributeObject()->isStatic()) {
            [$alias, $table, $joinConditions] = $this->_getMappingData();
            $aliasForExistenceCheck = is_array($alias) ? $alias[0] : $alias;

            if (strpos($select->assemble(), '`' . $aliasForExistenceCheck . '`') !== false) {
                return $this;
            }

            if (is_array($alias) && is_array($joinConditions)) {
                $select->joinLeft([$alias[0] => $table], $joinConditions[0], []);
                $select->joinLeft([$alias[1] => $table], $joinConditions[1], []);
            } else {
                $select->joinLeft([$alias => $table], $joinConditions, []);
            }
        }

        return $this;
    }

    protected function _getAttributeCode()
    {
        return $this->getAttributeObject()->getAttributeCode();
    }

    protected function isCanUseFindInSet(): bool
    {
        return $this->isSelectFromIndexTable();
    }

    protected function _getAlias()
    {
        if (!$this->indexKey) {
            if ($this->isUsePrimaryTableAlias()) {
                $this->indexKey = 'e';
            } else {
                parent::_getAlias();
            }
        }

        return $this->indexKey;
    }

    private function isUsePrimaryTableAlias(): bool
    {
        return $this->getAttributeObject()->isStatic()
            && $this->getAttributeObject()->getAttributeCode() !== 'category_ids';
    }
}
