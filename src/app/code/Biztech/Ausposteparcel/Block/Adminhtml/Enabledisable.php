<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Biztech\Ausposteparcel\Helper\Data;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store;

class Enabledisable extends Field
{
    protected $helper;
    protected $resourceConfig;
    protected $web;
    protected $store;

    /**
     * EnableDisable constructor.
     * @param Context $context
     * @param Data $helper
     * @param Config $resourceConfig
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Website $web
     * @param Store $store
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        Config $resourceConfig,
        Website $web,
        Store $store,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
        $this->web = $web;
        $this->resourceConfig = $resourceConfig;
        $this->store = $store;
        parent::__construct($context, $data);
    }

    public function setStoreManager($storeManager)
    {
        $this->storeManager = $storeManager;
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $websites = $this->helper->getAllWebsites();
        if (!empty($websites)) {
            $websiteId = $this->getRequest()->getParam('website', 0);
            if ($websiteId === 0) {
                $storeid = $this->getRequest()->getParam('store', 0);
                if ($storeid === 0) {
                    return $html = $element->getElementHtml();
                }
                $store = $this->store->load($storeid);
                if ($store && in_array($store->getStoreId(), $websites)) {
                    $html = $element->getElementHtml();
                } else {
                    $html = '<strong class="required" style="color:red;">' . __('Please select store from the activation tab, If store is not listed then Please buy an additional domains') . '</strong>';
                    return $html;
                }
            } else {
                $activated = false;

                $website = $this->web->load($websiteId);
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        if (in_array($store->getStoreId(), $websites)) {
                            $activated = true;
                            $html = $element->getElementHtml();
                            return $html;
                        }
                    }
                }
                if ($activated) {
                    $html = $element->getElementHtml();
                } else {
                    $html = '<strong class="required" style="color:red;">' . __('Please select store from the activation tab, If store is not listed then Please buy an additional domains') . '</strong>';
                    return $html;
                }
            }
        } else {
            $websiteCode = $this->_request->getParam('website');
            $websiteId = $this->store->load($websiteCode)->getWebsiteId();
            $isEnabled = $this->storeManager->getWebsite($websiteId)->getConfig('ausposteParcel/activation/key');
            if ($isEnabled != null || $isEnabled != '') {
                $html = sprintf('<strong class="required" style="color:red;">%s</strong>', __('Please select store from the activation tab, If store is not listed then Please buy an additional domains'));
                $moduleStatus = $this->resourceConfig;
                $moduleStatus->saveConfig('carriers/ausposteParcel/active', 0, 'default', 0);
            } else {
                $html = sprintf('<strong class="required" style="color:red;">%s</strong>', __('Please enter a valid key'));
            }
        }
        return $html;
    }
}
