<?php

namespace Meetanshi\ShippingRules\Controller\Adminhtml\Rule;

use Meetanshi\ShippingRules\Controller\Adminhtml\Rule;

class Index extends Rule
{
    public function execute()
    {
        $this->_view->loadLayout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Meetanshi_ShippingRules::rule');
        $resultPage->getConfig()->getTitle()->prepend(__('Shipping Rules'));
        $resultPage->addBreadcrumb(__('Shipping Rules'), __('Shipping Rules'));
        $this->_addContent($this->_view->getLayout()->createBlock('\Meetanshi\ShippingRules\Block\Adminhtml\Rule'));
        $this->_view->renderLayout();
    }
}
