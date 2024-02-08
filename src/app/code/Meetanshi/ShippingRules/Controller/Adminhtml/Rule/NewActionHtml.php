<?php

namespace Meetanshi\ShippingRules\Controller\Adminhtml\Rule;

use Meetanshi\ShippingRules\Controller\Adminhtml\Rule;

class NewActionHtml extends Rule
{
    public function execute()
    {
        $this->newConditions('actions');
    }
}
