<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Ausposteparcel\Model;

use Magento\Framework\DataObject\IdentityInterface;

/**
 * Managesuppliertab managesupplier model
 */
class Nonauspost extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = '';
    public function _construct()
    {
        parent::_construct();
        $this->_init('Biztech\Ausposteparcel\Model\Cresource\Nonauspost');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
