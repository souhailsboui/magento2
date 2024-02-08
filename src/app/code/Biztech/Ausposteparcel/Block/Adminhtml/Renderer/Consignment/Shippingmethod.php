<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Shippingmethod extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Context $Context,
        array $data = []
    ) {
        $this->scopeConfig = $Context->getScopeConfig();
        parent::__construct($Context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $shipping_method = $row->getData('shipping_method');
        if ($shipping_method == "") {
            return $row->getData('shipping_description');
        }
        $shipping_method = explode('_', $shipping_method);
        
        $charge_code = $shipping_method[1];
        $method = $row->getData('shipping_description');
        
        if ($shipping_method[0] == 'ausposteParcel') {
            $title = $this->scopeConfig->getValue('carriers/ausposteParcel/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $method = str_replace($title, '', $method);
            $method = str_replace(' - ', '', $method);
        }

        $display = $method . ' - ' . $charge_code;

        if ($shipping_method[0] != 'ausposteParcel') {
            $charge_code = $row->getData('general_ausposteParcel_shipping_chargecode');
            if (!empty($charge_code)) {
                $display = $method . ' - ' . $charge_code;
            } else {
                $display = $method;
            }
        }

        return $display;
    }
}
