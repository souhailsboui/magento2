<?php

namespace Biztech\Ausposteparcel\Model;

class Auspostlabel extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Biztech\Ausposteparcel\Model\Cresource\Auspostlabel');
    }
}
