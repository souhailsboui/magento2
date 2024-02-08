<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Freeshipping;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Shippingcost extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $shippingCost = $row->getData($this->getColumn()->getIndex());
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
        return $priceHelper->currency($shippingCost, true, false);
    }
}
