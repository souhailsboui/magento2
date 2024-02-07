<?php

namespace Biztech\Ausposteparcel\Model;

use Magento\Framework\DataObject\IdentityInterface;

class Article extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = '';
    public function _construct()
    {
        parent::_construct();
        $this->_init('Biztech\Ausposteparcel\Model\Cresource\Article');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
