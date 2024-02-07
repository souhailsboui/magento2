<?php

namespace Meetanshi\ShippingRestrictions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Rule extends AbstractDb
{
    public function massChangeStatus($ids, $status)
    {
        $database = $this->getConnection();
        $ids = array_map('intval', $ids);
        $database->update(
            $this->getMainTable(),
            ['is_active' => $status],
            'rule_id IN(' . implode(',', $ids) . ') '
        );

        return true;
    }

    public function getAttributes()
    {
        $database = $this->getConnection();
        $table = $this->getTable('meetanshi_shippingrestrict_attributes');

        $select = $database->select()->from($table, new \Zend_Db_Expr('DISTINCT code'));
        return $database->fetchCol($select);
    }

    public function saveAttributes($id, $attributes)
    {
        $database = $this->getConnection();
        $table = $this->getTable('meetanshi_shippingrestrict_attributes');

        $database->delete($table, ['rule_id=?' => $id]);

        $data = [];
        foreach ($attributes as $code) {
            $data[] = [
                'rule_id' => $id,
                'code' => $code,
            ];
        }
        $database->insertMultiple($table, $data);

        return $this;
    }

    protected function _construct()
    {
        $this->_init('meetanshi_shippingrestrict_rules', 'rule_id');
    }
}
