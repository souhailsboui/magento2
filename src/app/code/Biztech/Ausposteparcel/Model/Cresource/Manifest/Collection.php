<?php

namespace Biztech\Ausposteparcel\Model\Cresource\Manifest;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            'Biztech\Ausposteparcel\Model\Manifest',
            'Biztech\Ausposteparcel\Model\Cresource\Manifest'
        );
    }
}
