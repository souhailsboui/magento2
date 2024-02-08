<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RuleIndex extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_reports_rule_index';

    public const RULE_ID = 'rule_id';
    public const PRODUCT_ID = 'product_id';
    public const STORE_ID = 'store_id';

    /**
     * @return $this|void
     */
    protected function _construct()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getMainTable()
    {
        return $this->getTable(self::MAIN_TABLE);
    }

    /**
     * @return $this
     */
    public function cleanAllIndex()
    {
        $this->getConnection()->delete(
            $this->getMainTable()
        );

        return $this;
    }

    /**
     * @param array $ruleIds
     *
     * @return $this
     */
    public function cleanByRuleIds($ruleIds)
    {
        return $this->clean(self::RULE_ID, $ruleIds);
    }

    /**
     * @param array $productIds
     *
     * @return $this
     */
    public function cleanByProductIds($productIds)
    {
        return $this->clean(self::PRODUCT_ID, $productIds);
    }

    /**
     * @param string $field
     * @param array $values
     *
     * @return $this
     */
    private function clean($field, $values)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [$field . ' IN (?)' => $values]
        );

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function insertIndexData(array $data)
    {
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data);

        return $this;
    }

    /**
     * @param int $ruleId
     * @param int $storeId
     *
     * @return array
     */
    public function getAppliedProducts($ruleId, $storeId)
    {
        $sql = $this->getConnection()->select()->from($this->getMainTable(), self::PRODUCT_ID)
            ->where(self::RULE_ID . ' = ?', $ruleId);
        if ($storeId) {
            $sql->where(self::STORE_ID . ' = ?', $storeId);
        }

        return $this->getConnection()->fetchCol($sql);
    }
}
