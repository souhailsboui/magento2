<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Freeshipping;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Range extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $from = $row->getData($this->getColumn()->getIndex());
        $to = $row->getData('to_amount');
        $html = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
        if (!empty($from) && $from > 0) {
            if (!empty($to) && $to > 0) {
                $html = $priceHelper->currency($from, true, false) . ' - ' . $priceHelper->currency($to, true, false);
            } else {
                $html = '>= ' . $priceHelper->currency($from, true, false);
            }
        }
        return $html;
    }
}
