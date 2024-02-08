<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Catalog\Model\ResourceModel\Eav;

use Amasty\Base\Model\Serializer;
use \Magento\Catalog\Model\ResourceModel\Eav\Attribute as MagentoAttribute;

class Attribute
{
    // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
    public const CONDITION_TYPE_COMBINE = 'Amasty\VisualMerch\Model\Rule\Condition\Combine';
    // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
    public const CONDITION_TYPE_PRODUCT = 'Amasty\VisualMerch\Model\Rule\Condition\Product';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product\Processor
     */
    private $categoryProductProcessor;

    /**
     * @var MagentoAttribute
     */
    private $attribute;

    public function __construct(
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Indexer\Category\Product\Processor $categoryProductProcessor
    ) {
        $this->serializer = $serializer;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->categoryProductProcessor = $categoryProductProcessor;
    }

    /**
     * @param MagentoAttribute $attribute
     * @return array
     */
    public function afterDelete(MagentoAttribute $attribute, $result)
    {
        $this->attribute = $attribute;
        $attributeCode = $attribute->getAttributeCode();
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToFilter('amasty_dynamic_conditions', ['like' => "%" . $attributeCode . "%"]);
        foreach ($collection as $category) {
            $conditions = $this->serializer->unserialize($category->getData('amasty_dynamic_conditions'));
            if (isset($conditions['conditions']) && !empty($conditions['conditions'])) {
                foreach ($conditions['conditions'] as $key => &$condition) {
                    if (!$this->fixConditionsRecursive($condition)) {
                        unset($conditions['conditions'][$key]);
                    }
                }
                $category->setData('amasty_dynamic_conditions', $this->serializer->serialize($conditions));
                $attribute = $this->eavConfig->getAttribute(
                    \Magento\Catalog\Model\Category::ENTITY,
                    'amasty_dynamic_conditions'
                );
                $attribute->getEntity()->saveAttribute($category, $attribute->getName());
            }
        }

        $this->categoryProductProcessor->markIndexerAsInvalid();

        return $result;
    }

    /**
     * @param $condition
     * @return bool
     */
    private function fixConditionsRecursive(&$condition)
    {
        if (isset($condition['type'])
            && $condition['type'] == self::CONDITION_TYPE_COMBINE
            && isset($condition['conditions'])
        ) {
            foreach ($condition['conditions'] as $key => &$childCondition) {
                if (!$this->fixConditionsRecursive($childCondition)) {
                    unset($condition['conditions'][$key]);
                }
            }
            if (empty($condition['conditions'])) {
                return false;
            }
        } elseif ($condition['type'] == self::CONDITION_TYPE_PRODUCT) {
            if (isset($condition['attribute'])
                && !empty($condition['attribute'])
                && $condition['attribute'] !== 'null'
            ) {
                return $this->validateAttribute($condition['attribute']);
            }
        }
        return true;
    }

    /**
     * @param $attributeCode
     * @return bool
     */
    private function validateAttribute($attributeCode)
    {
        return $this->attribute->getAttributeCode() !== $attributeCode;
    }
}
