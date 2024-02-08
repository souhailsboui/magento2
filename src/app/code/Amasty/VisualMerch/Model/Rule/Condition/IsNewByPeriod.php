<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */
namespace Amasty\VisualMerch\Model\Rule\Condition;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\EntityManager\MetadataPool;

class IsNewByPeriod extends AbstractCondition
{
    public const ATTRIBUTE_CODE = 'news_by_period';

    /**
     * @var string
     */
    protected $_inputType = 'select';

    /**
     * @var string
     */
    private $productIdLink;

    /**
     * @var string
     */
    private $aliasFrom;

    /**
     * @var string
     */
    private $dAliasFrom;

    /**
     * @var string
     */
    private $aliasTo;

    /**
     * @var string
     */
    private $dAliasTo;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        MetadataPool $metadataPool,
        array $data = []
    ) {
        $this->productIdLink = $metadataPool->getMetadata(ProductInterface::class)->getLinkField();

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
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getAttributeElementHtml()
    {
        return __('Is New');
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return $this->_inputType;
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return $this->_inputType;
    }

    /**
     * @return string
     */
    protected function _getAttributeCode()
    {
        return self::ATTRIBUTE_CODE;
    }

    /**
     * @return $this
     */
    protected function _prepareValueOptions()
    {
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');

        $selectOptions = [
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 0, 'label' => __('No')]
        ];

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    /**
     * @param Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        $select = $productCollection->getSelect();
        $alias = $this->_getAlias();

        if (strpos($select->assemble(), '`' . $alias . '`') !== false) {
            return $this;
        }

        $this->aliasFrom = $alias . '_from';
        $this->dAliasFrom = 'tad_' . $this->aliasFrom;
        $this->aliasTo = $alias . '_to';
        $this->dAliasTo = 'tad_' . $this->aliasTo;

        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $tmp = 'tmp_merch_news';
        $fieldFrom = new \Zend_Db_Expr('IFNULL(' . $this->aliasFrom . '.value,' . $this->dAliasFrom . '.value)');
        $fieldTo = new \Zend_Db_Expr('IFNULL(' . $this->aliasTo . '.value,' . $this->dAliasTo . '.value)');

        $conditionFrom = $this->getOperatorCondition($tmp, '<=', $todayEndOfDayDate);
        $limit = 1;
        $conditionFrom = str_replace('`' . $tmp . '`', $fieldFrom, $conditionFrom, $limit);

        $conditionTo = $this->getOperatorCondition($tmp, '>=', $todayStartOfDayDate);
        $limit = 1;
        $conditionTo = str_replace('`' . $tmp . '`', $fieldTo, $conditionTo, $limit);

        if ((bool)$this->getValue() ^ $this->getOperatorForValidate() == '==') {
            //negative condition
            $this->_condition
                = "!( $conditionFrom ) OR !( $conditionTo ) OR ( $fieldFrom  IS NULL) OR ( $fieldTo  IS NULL)";
        } else {
            $this->_condition = '(' . $conditionFrom . ') and (' . $conditionTo . ')';
        }

        return $this->join($select);
    }

    /**
     * @param $select
     * @return $this
     */
    protected function join($select)
    {
        $attributeFrom = $this->_config->getAttribute(ProductModel::ENTITY, 'news_from_date');
        $attributeTo = $this->_config->getAttribute(ProductModel::ENTITY, 'news_to_date');
        $mapTpl = 'e.entity_id = %1$s.'
            . $this->productIdLink
            . ' AND %1$s.attribute_id = %2$d AND %1$s.store_id = %3$d';
        $storeId = $this->getStoreManager()->getStore()->getId();

        $select->joinLeft(
            [
                $this->dAliasFrom => $this->_productResource->getTable('catalog_product_entity_datetime')
            ],
            sprintf(
                $mapTpl,
                $this->dAliasFrom,
                $attributeFrom->getId(),
                0
            ),
            []
        );
        $select->joinLeft(
            [
                $this->aliasFrom => $this->_productResource->getTable('catalog_product_entity_datetime')
            ],
            sprintf(
                $mapTpl,
                $this->aliasFrom,
                $attributeFrom->getId(),
                $storeId
            ),
            []
        );

        $select->joinLeft(
            [
                $this->dAliasTo => $this->_productResource->getTable('catalog_product_entity_datetime')
            ],
            sprintf(
                $mapTpl,
                $this->dAliasTo,
                $attributeTo->getId(),
                0
            ),
            []
        );
        $select->joinLeft(
            [
                $this->aliasTo => $this->_productResource->getTable('catalog_product_entity_datetime')
            ],
            sprintf(
                $mapTpl,
                $this->aliasTo,
                $attributeTo->getId(),
                $storeId
            ),
            []
        );

        return $this;
    }
}
