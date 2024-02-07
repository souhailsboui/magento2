<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;
use Zend_Db_Expr;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @var bool
     */
    private $visibleOnlyFlag = true;

    /**
     * @var bool
     */
    private $isClonned = false;

    /**
     * @var bool
     */
    private $useDefaultSortingFlag = false;

    /**
     * @var int[]|null
     */
    private $idsStorage = null;

    /**
     * @param bool $visibleOnly
     * @return array
     */
    public function getProductIds($visibleOnly = true)
    {
        if (!$this->isClonned) {
            if ($this->idsStorage === null) {
                $clonned = clone $this;
                $result = $clonned->setIsClonned(true)->getProductIds();

                $this->idsStorage = $result;
            }

            return $this->idsStorage;
        }
        $this->_beforeLoad();
        $this->_renderFilters();
        $this->_renderOrders();
        $select = $this->getSelect();
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);

        $columns = $this->getColumnsUsedForOrder($select);
        array_unshift($columns, [
            'e',
            'entity_id',
            null
        ]);
        $select->setPart(Select::COLUMNS, $columns);

        return $this->getConnection()->fetchCol($select);
    }

    public function setIsVisibleOnlyFilter(bool $visibleOnly): void
    {
        $this->visibleOnlyFlag = $visibleOnly;
    }

    /**
     * @inheritdoc
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        /**
         * Keep "order" part in getAllIds() method for merchandising search.
         * Using in Amasty/VisualMerch/Block/Adminhtml/Products/Listing::search()
         */
        $idsSelect->setPart(Select::ORDER, $this->getSelect()->getPart(Select::ORDER));
        $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        if ($this->visibleOnlyFlag) {
            $this->_eventManager->dispatch(
                'amasty_merchandiser_collection_before_load',
                ['data_object' => $this]
            );
        }

        return parent::_beforeLoad();
    }

    public function clear()
    {
        $this->idsStorage = null;
        return parent::clear();
    }

    /**
     * @return bool
     */
    public function getIsClonned()
    {
        return $this->isClonned;
    }

    /**
     * @param bool $isClonned
     * @return $this
     */
    public function setIsClonned($isClonned = false)
    {
        $this->isClonned = $isClonned;
        return $this;
    }

    public function setUseDefaultSorting(bool $useDefaultSorting = false): void
    {
        $this->useDefaultSortingFlag = $useDefaultSorting;
    }

    public function getUseDefaultSorting(): bool
    {
        return (bool) $this->useDefaultSortingFlag;
    }

    private function getColumnsUsedForOrder(Select $select): array
    {
        $columns = [];
        $orders = $select->getPart(Select::ORDER);
        if (!empty($orders)) {
            $orderAliases = [];
            foreach ($orders as $order) {
                if ($order instanceof Zend_Db_Expr) {
                    $order = $order->__toString();
                    $expr = '/(.*\W)(' . Select::SQL_ASC . '|' . Select::SQL_DESC . ')\b/si';
                    preg_match($expr, $order, $matches);
                    if ($matches) {
                        $orderAliases[] = trim($matches[1]);
                    } else {
                        $orderAliases[] = trim($order);
                    }
                } elseif (is_array($order) && isset($order[0])) {
                    $orderAliases[] = $order[0];
                }
            }

            $columns = $select->getPart(Select::COLUMNS);
            foreach ($columns as $columnId => $column) {
                $columnAlias = $column[2] ?? null;
                if (!in_array($columnAlias, $orderAliases, true)) {
                    unset($columns[$columnId]);
                }
            }
        }

        return array_values($columns);
    }
}
