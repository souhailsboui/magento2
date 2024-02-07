<?php

namespace Biztech\Ausposteparcel\Model\Cresource\Articletype;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            'Biztech\Ausposteparcel\Model\Articletype',
            'Biztech\Ausposteparcel\Model\Cresource\Articletype'
        );
    }
}
