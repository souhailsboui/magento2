<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Freeshipping;

class NewAction extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
