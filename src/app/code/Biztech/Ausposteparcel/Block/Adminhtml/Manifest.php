<?php
namespace Biztech\Ausposteparcel\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Manifest extends Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_manifest';
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_headerText = __('Manifest');
        parent::_construct();
        $this->removeButton('add');
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
