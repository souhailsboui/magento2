<?php declare(strict_types=1);

namespace Machship\Fusedship\Model\ResourceModel\SalesOrder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Machship\Fusedship\Model\SalesOrder;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(SalesOrder::class, \Machship\Fusedship\Model\ResourceModel\SalesOrder::class);
    }
}
