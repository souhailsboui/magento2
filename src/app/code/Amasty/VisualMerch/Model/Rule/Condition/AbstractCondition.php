<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition;

class AbstractCondition extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @var string
     */
    protected $indexKey;

    /**
     * @var string
     */
    protected $_condition;

    /**
     * @var string
     */
    private $value;

    protected function _getSelectOperator($field, $operator, $value)
    {
        switch ($operator) {
            case '!=':
            case '>=':
            case '<=':
            case '>':
            case '<':
                $selectOperator = sprintf('%s?', $operator);
                break;
            case '{}':
            case '!{}':
                if (preg_match('/^.*(category_id)$/', $field) && is_array($value)) {
                    $selectOperator = ' IN (?)';
                } else {
                    if (is_array($value)) {
                        $selectOperator = ' IN (?)';
                    } else {
                        $selectOperator = ' LIKE ?';
                        $value          = '%' . $value . '%';
                    }
                }
                if (substr($operator, 0, 1) == '!') {
                    $selectOperator = ' NOT' . $selectOperator;
                }
                break;
            case '()':
                if (!is_array($value)) {
                    $value = array_map('trim', explode(',', $value));
                }
                $selectOperator = ' IN(?)';
                break;
            case '!()':
                if (!is_array($value)) {
                    $value = array_map('trim', explode(',', $value));
                }
                $selectOperator = ' NOT IN(?)';
                break;
            case '!':
                $selectOperator = 'IS NOT NULL';
                break;
            default:
                $selectOperator = '=?';
                break;
        }

        $this->value = $value;

        return $selectOperator;
    }

    public function getOperatorCondition($field, $operator, $value)
    {
        $result = ' true ';
        $adapter = $this->_productResource->getConnection();

        $this->value = $value;
        $selectOperator = $this->_getSelectOperator($field, $operator, $value);

        $field = $adapter->quoteIdentifier($field);

        if (is_array($this->value) && in_array($operator, ['==', '!=', '>=', '<=', '>', '<'])) {
            $results = [];
            foreach ($this->value as $v) {
                $results[] = $adapter->quoteInto("{$field}{$selectOperator}", $v);
            }
            $result = implode(' AND ', $results);
        } else {
            if ($this->isCanUseFindInSet()
                && is_array($this->value)
                && in_array($operator, ['()', '!()', '{}', '!{}', '[]', '![]'])
            ) {
                $resultArr = [];

                foreach ($this->value as $option) {
                    $condition = in_array($operator, ['()', '{}', '[]']) ? ' <> 0' : ' = 0';

                    $resultArr[] = $adapter->quoteInto("FIND_IN_SET(?, {$field}) {$condition}", $option);
                }

                if (count($resultArr) > 0) {
                    if (in_array($operator, ['()', '!{}', '![]'])) {
                        $result = "(" . implode(' OR ', $resultArr) . ")";
                    } else {
                        $result = "(" . implode(' AND ', $resultArr) . ")";
                    }
                }
            } else {
                $result = $adapter->quoteInto("{$field}{$selectOperator}", $this->value);
            }
        }

        return $result;
    }

    protected function isCanUseFindInSet(): bool
    {
        return $this->getAttributeObject()->getFrontendInput() === 'multiselect';
    }

    protected function _getAttributeCode()
    {
        return '';
    }

    protected function _getAlias()
    {
        if (!$this->indexKey) {
            $this->indexKey = $this->getAliasPrefix() . '_' . 'amasty_dynamic_products_idx';
        }

        return $this->indexKey;
    }

    public function collectConditionSql()
    {
        return $this->_condition;
    }

    /**
     * @return string
     */
    public function getAttribute()
    {
        return (string)parent::getAttribute();
    }

    protected function getAliasPrefix(): string
    {
        return 'attr_' . $this->_getAttributeCode();
    }
}
