<?php

namespace Meetanshi\ShippingRules\Controller\Adminhtml\Rule;

use Meetanshi\ShippingRules\Controller\Adminhtml\Rule;

class NewConditionHtml extends Rule
{
    public function execute()
    {
        $this->newConditions('conditions');
    }
}
