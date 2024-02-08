<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Labelprinted extends \Magento\Framework\View\Element\Template
{
    protected $storeManagerInterface;
    protected $scopeconfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeconfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        array $data = []
    ) {
        $this->scopeconfig = $scopeconfig;
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context, $data);
    }

    public function getBaseUrl()
    {
        return $this->storeManagerInterface->getStore()->getBaseUrl();
    }
    public function getValue($path)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeconfig->getValue($path, $storeScope);
    }
}
