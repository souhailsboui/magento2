<?php

namespace Biztech\Ausposteparcel\Model\Cresource\Article;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            'Biztech\Ausposteparcel\Model\Article',
            'Biztech\Ausposteparcel\Model\Cresource\Article'
        );
    }
}
