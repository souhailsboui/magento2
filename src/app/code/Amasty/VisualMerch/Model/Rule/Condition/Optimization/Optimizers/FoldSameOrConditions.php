<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition\Optimization\Optimizers;

use Amasty\VisualMerch\Model\Rule\Condition\Optimization\ConditionsOptimizerInterface;
use Amasty\VisualMerch\Model\Rule\Condition\Product as ProductCondition;
use Amasty\VisualMerch\Model\Rule\Condition\ProductFactory as ProductConditionFactory;
use Magento\Rule\Model\Condition\Combine as MagentoCombineConditions;
use Magento\SalesRule\Api\Data\ConditionInterface;

class FoldSameOrConditions implements ConditionsOptimizerInterface
{
    public const EXCLUDED_ATTRIBUTE_TYPES = ['date'];

    public const EQUAL_OPERATOR = '==';
    public const NOT_EQUAL_OPERATOR = '!=';
    public const IN_CONDITION = '()';
    public const NIN_CONDITION = '!()';

    /**
     * @var ProductConditionFactory
     */
    private $productConditionFactory;

    /**
     * @var array|string[]
     */
    private $excludedAttributeTypes;

    public function __construct(
        ProductConditionFactory $productConditionFactory,
        array $excludedAttributeTypes = self::EXCLUDED_ATTRIBUTE_TYPES
    ) {
        $this->productConditionFactory = $productConditionFactory;
        $this->excludedAttributeTypes = $excludedAttributeTypes;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param MagentoCombineConditions $conditions
     */
    public function optimize(MagentoCombineConditions $conditions): void
    {
        $aggregator = $conditions->getAggregator();
        $subConditions = $conditions->getConditions();

        if ($aggregator === ConditionInterface::AGGREGATOR_TYPE_ANY) {
            $foldedConditions = [
                self::EQUAL_OPERATOR => [],
                self::NOT_EQUAL_OPERATOR => []
            ];

            foreach ($subConditions as $key => $condition) {
                if ($condition instanceof ProductCondition
                    && !in_array($condition->getInputType(), $this->excludedAttributeTypes)
                ) {
                    $foldedConditions[$condition->getOperator()][$condition->getAttribute()][] = [
                        'key' => $key,
                        'value' => $condition->getValue()
                    ];
                }
            }

            $operatorsMap = [
                self::EQUAL_OPERATOR => self::IN_CONDITION,
                self::NOT_EQUAL_OPERATOR => self::NIN_CONDITION
            ];

            foreach ($operatorsMap as $unfoldedOperator => $foldingOperator) {
                foreach ($foldedConditions[$unfoldedOperator] as $attribute => $valuesByAttribute) {
                    if (count($valuesByAttribute) > 1) {
                        $values = [];

                        foreach ($valuesByAttribute as $value) {
                            $values[] = $value['value'];
                            unset($subConditions[$value['key']]);
                        }

                        $productCondition = $this->productConditionFactory->create();
                        $productCondition->setAttribute($attribute);
                        $productCondition->setValue(array_unique($values));
                        $productCondition->setOperator($foldingOperator);
                        $subConditions[] = $productCondition;
                    }
                }
            }
        }

        foreach ($subConditions as $condition) {
            if ($condition instanceof MagentoCombineConditions) {
                $this->optimize($condition);
            }
        }

        $conditions->setConditions($subConditions);
    }
}
