<?php

namespace Meetanshi\ShippingRules\Controller\Adminhtml\Rule;

use Magento\Framework\App\ResponseInterface;
use Meetanshi\ShippingRules\Controller\Adminhtml\Rule;

class NewAction extends Rule
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
