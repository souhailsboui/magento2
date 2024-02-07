<?php

namespace Biztech\Ausposteparcel\Model\Cresource;

class Auspostlabel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        $this->_resources = $context->getResources();
        parent::__construct(
            $context,
            $connectionName
        );
    }

    public function _construct()
    {
        // Note that the label_id refers to the key field in your database table.
        $this->_init('auspost_label', 'label_id');
    }

    public function truncate()
    {
        $this->_getWriteAdapter()->query('TRUNCATE TABLE ' . $this->getMainTable());
        return $this;
    }

    public function changeAutoIncrement($increment = 1)
    {
        //$this->_getWriteAdapter()->query('ALTER TABLE ' . $this->getMainTable() . ' AUTO_INCREMENT = ' . $increment);
        $connection = $this->_resources->getConnection();
        $sql = 'ALTER TABLE ' . $this->getMainTable() . ' AUTO_INCREMENT = ' . $increment;
        $connection->query($sql);
    }
}
