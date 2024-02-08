<?php

namespace MageMe\WebForms\Block\Adminhtml\Store;

use MageMe\WebForms\Api\Data\LogicInterface;

class Switcher extends \Magento\Backend\Block\Store\Switcher
{
    /**
     * @inheritDoc
     */
    public function getWebsites()
    {
        if (!$this->getRequest()->getParam(LogicInterface::ID)) {
            return [];
        }
        return parent::getWebsites();
    }
}