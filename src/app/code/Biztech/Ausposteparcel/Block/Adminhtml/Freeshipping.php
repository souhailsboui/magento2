<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Freeshipping extends Container
{

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_freeshipping';
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_headerText = __('Freeshipping Rules Management');
        parent::_construct();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->ausposteparcelHelper = $objectManager->get('Biztech\Ausposteparcel\Helper\Data');
        $this->messageManager = $objectManager->get('Magento\Framework\Message\ManagerInterface');
        if (!empty($this->ausposteparcelHelper->getAllWebsites())) {
            $this->buttonList->add(
                'export_csv',
                [
                'label' => __('Add New Rule'),
                'style' => 'background-color: #eb5202; color: #ffffff; height:45px; width:150px; font-size:16px; padding:0.5rem 0rem 0.6rem 0rem;',
                'onclick' => "setLocation('{$this->getUrl('*/*/new')}')"
                    ]
            );
            $this->removeButton('add');
            return $this;
        } else {
            $this->removeButton('add');
            $this->messageManager->addError(__('Extension- Australia Post Parcel Send is not enabled. Please enable it from Store > Configuration > Sales > Shipping Methods -> Appjetty Australia Post Parcel Send.'));
            return $this;
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
