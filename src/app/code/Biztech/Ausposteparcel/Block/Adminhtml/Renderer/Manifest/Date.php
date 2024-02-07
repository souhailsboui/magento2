<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest;

use Magento\Framework\App\Action\Action;

class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $orderRepository;
    protected $_storeManager;
    protected $datetime;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime
    ) {
        $this->_storeManager = $storeManager;
        $this->datetime = $datetime;
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $date = $row->getData($this->getColumn()->getIndex());
        if ($date) {
            $dateTimestamp = $this->datetime->timestamp(strtotime($date));
            return date('m/d/Y H:i:s', $dateTimestamp);
        }
    }
}
