<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Config\Form\Renderer;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Biztech\Ausposteparcel\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class Website extends Field
{
    protected $scopeConfig;
    protected $helper;
    protected $encrypt;
    protected $storeManager;

    /**
     * Website constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface $encrypt
     * @param \Biztech\Auspost\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encrypt,
        Data $helper,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->encrypt = $encrypt;
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = '';
        $eleName = $element->getName();
        $element->setName($eleName . '[]');
        $getDataInfo = $this->helper->getDataInfo();
        if (isset($getDataInfo->dom) && (int) ($getDataInfo->c) > 0 && (int) ($getDataInfo->suc) == 1) {
            return $element->getElementHtml();
        } else {
            $html = sprintf('<strong class="required" style="color:red;">%s</strong>', __('Please enter a valid key'));
        }
        return $html;
    }
}
