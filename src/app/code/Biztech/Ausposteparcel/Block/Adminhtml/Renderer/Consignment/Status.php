<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $Connection;
    
    public function __construct(
        \Magento\Framework\App\ResourceConnection $Connection
    ) {
        $this->Connection = $Connection;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $status = $row->getData($this->getColumn()->getIndex());
        $readConnection = $this->Connection->getConnection();
        $table = $this->Connection->getTableName('sales_order_status');

        $query = "select label from {$table} where status = '{$status}'";
        return $readConnection->fetchOne($query);
    }
}
