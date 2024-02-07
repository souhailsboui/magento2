<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

class Order extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $orderRepository;
    protected $_backendUrl;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->_backendUrl = $backendUrl;
        $this->orderRepository = $orderRepository;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData('order_consignment');
        $values = explode('_', $value);
        $orderId = $values[0];
        $order = $this->orderRepository->get($orderId);
        $incrementId = $order->getIncrementId();
        $params = array('order_id' => $orderId);
        $orderLink = $this->_backendUrl->getUrl("sales/order/view/", $params);
        $html = '<a title='.$incrementId.' href="' . $orderLink . '">' . $incrementId . '</a>';
        return $html;
    }
}