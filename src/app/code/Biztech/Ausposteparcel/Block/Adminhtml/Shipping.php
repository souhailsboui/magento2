<?php
namespace Biztech\Ausposteparcel\Block\Adminhtml;

class Shipping extends \Magento\Framework\View\Element\Template
{
    protected $_helper;
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Biztech\Ausposteparcel\Helper\Info $helper, array $data = [])
    {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }
    public function callhelper()
    {
        return $this->_helper;
    }
}
