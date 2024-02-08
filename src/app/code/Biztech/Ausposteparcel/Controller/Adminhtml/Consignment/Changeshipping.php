<?php
namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Changeshipping extends Action
{
    protected $_resultPageFactory;
    protected $order;
    
    public function __construct(Context $context, PageFactory $resultPageFactory, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->order              = $order;
        parent::__construct($context);
    }
    public function execute()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        if ($order_id > 0) {
            $order = $this->order->load($order_id);
            $ausposteParcel_shipping_option = trim($this->getRequest()->getParam('ausposteParcel_shipping_option'));
            $ausposteParcel_shipping_option = base64_decode($ausposteParcel_shipping_option);
            $options = explode('###', $ausposteParcel_shipping_option);
            $order->setShippingMethod(trim($options[0]));
            $order->setShippingDescription(trim($options[1]));
            $order->save();
        }
    }
}
