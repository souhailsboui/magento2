<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Nonauspost extends Container
{

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_nonauspost'; /* block grid.php directory */
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_headerText = __('Nonauspost');
        parent::_construct();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->ausposteparcelHelper = $objectManager->get('Biztech\Ausposteparcel\Helper\Data');
        $this->messageManager = $objectManager->get('Magento\Framework\Message\ManagerInterface');
        if (!empty($this->ausposteparcelHelper->getAllWebsites())) {
            $this->_addButtonLabel = __('Add New');
            return $this;
        } else {
            $this->removeButton('add');
            $this->messageManager->addError(__('Extension- Australia Post Parcel Send is not enabled. Please enable it from Store > Configuration > Sales > Shipping Methods -> Appjetty Australia Post Parcel Send.'));
            return $this;
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
