<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Number extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $orderId = $row->getData('order_id');
        $orderLink = $this->_storeManager->getStore()->getURL('sales/order/view/order_id/' . $orderId . '/active_tab/auspost_eparcel');
        $html = '<a href="' . $orderLink . '" >' . $value . '</a>';
        return $html;
    }
}
