<?php

namespace Biztech\Ausposteparcel\Model\Config\Source;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Orderstatus extends \Magento\Framework\Data\Collection
{
    protected $_orderConfig;

    public function __construct(\Magento\Sales\Model\Order\Config $orderConfig)
    {
        $this->_orderConfig = $orderConfig;
    }

    public function toOptionArray()
    {
        $statuses = $this->_orderConfig->getStatuses();

        $allOrderStatuses[] = array('label' => 'Select Order Status', 'value' => '');
        foreach ($statuses as $key => $status) {
            $allOrderStatuses[] = array('label' => $status, 'value' => $key);
        }

        return $allOrderStatuses;
    }
}
