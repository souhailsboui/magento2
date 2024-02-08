<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition;

use Amasty\VisualMerch\Model\Rule\Condition\Stock\Qty as StockQty;
use Amasty\VisualMerch\Model\Rule\Condition\Stock\StockStatus;

class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $operatorsMap = [
        '==' => '!=',
        '!=' => '==',
        '>=' => '<',
        '<=' => '>',
        '>' => '<=',
        '<' => '>=',
        '{}' => '!{}',
        '!{}' => '{}',
        '()' => '!()',
        '!()' => '()',
    ];

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\VisualMerch\Model\Rule\Condition\ProductFactory $conditionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $conditionFactory, $data);
        $this->storeManager = $storeManager;
        $this->setType(Combine::class);
    }

    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();

        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Amasty\VisualMerch\Model\Rule\Condition\Product|' . $code,
                'label' => $label
            ];
        }
        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => Combine::class,
                    'label' => __('Conditions Combination'),
                ],
                [
                    'label' => __('Custom Fields'),
                    'value' => [
                        [
                            'label' => __('Is New (by a period)'),
                            'value' => IsNewByPeriod::class,
                        ],
                        [
                            'label' => __('Is New (by \'is_new\' attribute)'),
                            'value' => IsNew::class,
                        ],
                        [
                            'label' => __('Created (in days)'),
                            'value' => Created::class,
                        ],
                        [
                            'label' => __('In Stock'),
                            'value' => StockStatus::class,
                        ],
                        [
                            'label' => __('Is on Sale'),
                            'value' => Price\Sale::class,
                        ],
                        [
                            'label' => __('Qty'),
                            'value' => StockQty::class,
                        ],
                        [
                            'label' => __('Min Price'),
                            'value' => Price\Min::class,
                        ],
                        [
                            'label' => __('Max Price'),
                            'value' => Price\Max::class,
                        ],
                        [
                            'label' => __('Final Price'),
                            'value' => Price\FinalPrice::class,
                        ],
                        [
                            'label' => __('Rating'),
                            'value' => Rating::class,
                        ],
                    ]
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        return $conditions;
    }

    public function collectConditionSql()
    {
        $wheres = [];
        foreach ($this->getConditions() as $condition) {
            $where = $condition->collectConditionSql();
            if ($where) {
                $wheres[] = $where;
            }
        }

        if (empty($wheres)) {
            return '';
        }

        $delimiter = $this->getAggregator() == "all" ? ' AND ' : ' OR ';
        return '(' . implode($delimiter, $wheres) . ')';
    }

    /**
     * @param object $condition
     * @return $this
     */
    public function addCondition($condition)
    {
        $condition->setData('store_manager', $this->storeManager);
        return parent::addCondition($condition);
    }

    /**
     * @inheritdoc
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Product|Combine $condition */
            if (!$this->getValue()) {
                if ($condition instanceof Combine) {
                    $condition->setValue((int)!$condition->getValue());
                } else {
                    $condition->setOperator($this->operatorsMap[$condition->getOperator()]);
                }
            }
        }

        return parent::collectValidatedAttributes($productCollection);
    }
}
