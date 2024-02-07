<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Ordernumber extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public $order;
    protected $urlinterface;

    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Backend\Model\UrlInterface $urlinterface
    ) {
        $this->urlinterface = $urlinterface;
        $this->order = $order;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData('order_consignment');
        $values = explode('_', $value);
        $orderId = $values[0];
        $order = $this->order->load($orderId);
        $incrementId = $order->getIncrementId();
        $orderLink = $this->urlinterface->getUrl("*/sales/order/view", array('order_id' => $orderId));
        $html = '<a href="' . $orderLink . '">' . $incrementId . '</a>';
        return $html;
    }
}
