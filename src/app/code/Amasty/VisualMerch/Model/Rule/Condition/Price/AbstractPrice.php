<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */
namespace Amasty\VisualMerch\Model\Rule\Condition\Price;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class AbstractPrice extends \Amasty\VisualMerch\Model\Rule\Condition\AbstractCondition
{
    /**
     * @var string
     */
    protected $_inputType = 'numeric';

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = []
    ) {
        return parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat
        );
    }

    public function getAttributeElementHtml()
    {
        return __('Price');
    }

    protected function _getAttributeCode()
    {
        return 'price';
    }

    protected function _getCondition()
    {
        if (!$this->_condition) {
            $alias = $this->_getAlias();

            $value     = $this->getValue();
            $operator  = $this->getOperatorForValidate();

            $this->_condition = $this->getOperatorCondition(
                $alias . '.' . $this->_getAttributeCode(),
                $operator,
                $value
            );
        }
        return $this->_condition;
    }

    /**
     * @param Collection $productCollection
     * @return AbstractPrice|void
     */
    public function collectValidatedAttributes($productCollection)
    {
        $select = $productCollection->getSelect();
        $alias = $this->_getAlias();

        $this->_condition = $this->_getCondition();

        if (strpos($select->assemble(), sprintf('`%s`', $alias)) !== false) {
            return $this;
        }

        $select->joinLeft(
            [
                $alias => $this->_productResource->getTable('catalog_product_index_price')
            ],
            $this->_productResource->getConnection()->quoteInto(
                'e.entity_id = ' . $alias . '.entity_id AND ' . $alias . '.website_id = ?',
                $this->getStoreManager()->getStore()->getWebsiteId() /** @todo website id 0 or current */
            ),
            []
        );
    }

    public function collectConditionSql()
    {
        return $this->_getCondition();
    }

    protected function getAliasPrefix(): string
    {
        return 'price_index';
    }
}
