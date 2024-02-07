<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Consignment extends Container
{

    protected function _construct()
    {
        $this->_controller = 'adminhtml_consignment';
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_headerText = __('Consignment');
        parent::_construct();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->ausposteparcelHelper = $objectManager->get('Biztech\Ausposteparcel\Helper\Data');
        $this->messageManager = $objectManager->get('Magento\Framework\Message\ManagerInterface');
        if (!empty($this->ausposteparcelHelper->getAllWebsites())) {
            $this->_addButtonLabel = __('Add Consignment');
            $this->buttonList->add(
                'export_csv',
                [
                'label' => __('Dispatch'),
                'style' => 'background-color: #eb5202; color: #ffffff; height:45px; width:130px; font-size:16px; padding:0.5rem 0rem 0.6rem 0rem;',
                'onclick' => "setLocation('{$this->getUrl('*/*/despatch')}')"
                    ]
            );
        }
        $this->removeButton('add');
        return $this;
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
