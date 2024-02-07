<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

class Number extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $urlinterface;
    protected $ausposteParcelInfoHelper;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlinterface,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper
    ) {
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->urlinterface = $urlinterface;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $order = $row->getData('order_consignment');
        $values = explode('_', $order);
        $orderId = $values[0];


        $code = $this->ausposteParcelInfoHelper->getOrderCarrier($orderId);

        if ($code != 'ausposteParcel') {
            $html = '<button>' . __('Non Eparcel shipping method.') . '</button>';
            return $html;
        }

        $eparcel_consignment_id = $row->getData('eparcel_consignment_id');
        if ($eparcel_consignment_id != '' && $eparcel_consignment_id != null) {
            if ($row->getData('despatched')) {
                $orderLink = $this->urlinterface->getUrl('biztech_ausposteparcel/consignment/create', array('order_id' => $orderId, 'source' => 'grid', 'consignment_number' => $values[1]));
                $html = '<a href="' . $orderLink . '" title = "' . $eparcel_consignment_id . ' ( '. $values[1] .' ) Dispatched">' . $eparcel_consignment_id . ' ( '. $values[1] .' ) Dispatched</a>';
                return $html;
            } else {
                $orderLink = $this->urlinterface->getUrl('biztech_ausposteparcel/consignment/create', array('order_id' => $orderId, 'source' => 'grid', 'consignment_number' => $values[1]));
                $html = '<a href="' . $orderLink . '" title = "' . $eparcel_consignment_id . ' ( '. $values[1] .' )">' . $eparcel_consignment_id . ' ( '. $values[1] .' ) </a>';
                return $html;
            }
        } else {
            $value = $row->getData('consignment_number');
            if (!$value) {
                if ($this->ausposteParcelInfoHelper->isOrderShipped($orderId)) {
                    $order = $row->getData('order_consignment');
                    $values = explode('_', $order);
                    $orderId = $values[0];
                    $value = __('Create Consignment');
                    $orderLink = $this->urlinterface->getUrl('biztech_ausposteparcel/consignment/create', array('order_id' => $orderId, 'source' => 'grid'));
                } else {
                    $value = __('Create Shipment of order');
                    $orderLink = $this->urlinterface->getUrl('adminhtml/order_shipment/start', array('order_id' => $orderId));
                }
            } else {
                $orderId = $row->getData('order_id');
                $orderLink = $this->urlinterface->getUrl('biztech_ausposteparcel/consignment/create', array('order_id' => $orderId, 'source' => 'grid', 'consignment_number' => $value));
            }
            $html = '<a href="' . $orderLink . '" title = "' . $value . '" >' . $value . '</a>';
            return $html;
        }
    }
}
