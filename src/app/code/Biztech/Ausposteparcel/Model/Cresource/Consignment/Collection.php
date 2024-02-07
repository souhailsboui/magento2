<?php

namespace Biztech\Ausposteparcel\Model\Cresource\Consignment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            'Biztech\Ausposteparcel\Model\Consignment',
            'Biztech\Ausposteparcel\Model\Cresource\Consignment'
        );
    }
}
