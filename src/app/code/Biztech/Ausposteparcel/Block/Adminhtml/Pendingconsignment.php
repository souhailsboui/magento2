<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml;

class Pendingconsignment extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_pendingconsignment';
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_headerText = __('Pending Consignments');

        parent::_construct();
        $this->removeButton('add');
        $data = array(
            'label' => 'Back',
            'onclick' => "setLocation('" . $this->getUrl('biztech_ausposteparcel/manifestconsignments/index', array('manifest' => $this->getRequest()->getParam('manifest_number'))) . "')",
            'class' => 'back'
        );
        $this->addButton('back', $data, 0, 100, 'header');
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
