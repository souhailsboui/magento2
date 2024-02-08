<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Ui\DataProvider\Listing\Customers\Conversion;

use Amasty\Reports\Model\ResourceModel\Customers\Conversion\Collection;
use Amasty\Reports\Model\ResourceModel\Customers\Conversion\Grid\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Sql\ColumnValueExpressionFactory;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    public const FILTER_VISITORS = 'visitors';
    public const FILTER_ORDERS = 'orders';
    public const FILTER_CONVERSION = 'conversion';

    /**
     * @var ColumnValueExpressionFactory
     */
    private $columnValueExpressionFactory;

    public function __construct(
        ColumnValueExpressionFactory $columnValueExpressionFactory,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->columnValueExpressionFactory = $columnValueExpressionFactory;
    }

    /**
     * @var array
     */
    private $havingColumns = [
        'visitors' => Collection::VISITORS_EXPRESSION,
        'orders' => Collection::ORDERS_AMOUNT,
        'conversion' => Collection::CONVERSION_EXPRESSION
    ];

    /**
     * @var array
     */
    private $havingFilters = [];

    /**
     * @param \Magento\Framework\Api\Filter $filter
     *
     * @return mixed|void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'period') {
            $expression = $this->columnValueExpressionFactory->create(
                ['expression' => 'CONCAT(\',\',applied_rule_ids,\',\')']
            );
            $filter->setField($expression);
            $filter->setConditionType('like');
            $filter->setValue('%,' . $filter->getValue() . ',%');
        } elseif (in_array($filter->getField(), array_keys($this->havingColumns))) {
            $this->havingFilters[] = $filter;
            return $this;
        }

        parent::addFilter($filter);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $operations = [
            'gteq' => '>=',
            'lteq' => '<=',
            'like' => 'like'
        ];

        foreach ($this->havingFilters as $filter) {
            $fieldExpr = $this->havingColumns[$filter->getField()];
            $searchResult->getSelect()->having(
                $fieldExpr . ' ' . $operations[$filter->getConditionType()] . ' "' . $filter->getValue() . '"'
            );
        }

        $this->setTotalCount($searchResult);

        return parent::searchResultToOutput($searchResult);
    }

    private function setTotalCount(SearchResultInterface $searchResult): void
    {
        $countSelect = clone $searchResult->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect = $this->columnValueExpressionFactory->create(
            ['expression' => 'SELECT COUNT(*) as total_count from (' . $countSelect->assemble() . ') as count']
        );
        $totalCount = (int)$searchResult->getConnection()->fetchRow($countSelect)['total_count'];
        $searchResult->setTotalCount($totalCount);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $result = parent::getData();

        foreach ($result['items'] as &$orderItem) {
            $orderItem['conversion'] = round((float) $orderItem['conversion']) . '%';
        }

        return $result;
    }
}
