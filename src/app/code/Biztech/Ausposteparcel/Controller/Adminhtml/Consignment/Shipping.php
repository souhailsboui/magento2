<?php
namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Shipping extends Action
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
        $resultPage = $this->_resultPageFactory->create();
        $params     = $this->getRequest()->getParams();
        $order_id   = $params['orderId'];
        $order      = $this->order->load($order_id);
        
        $layout = $resultPage->getLayout()->createBlock('Biztech\Ausposteparcel\Block\Adminhtml\Shipping');
        try {
            $result["shipping_method"] = $layout->setData(array(
                "order" => $order,
                "order_id" => $order_id
            ))->setTemplate('ausposteParcel/changeshippingoption.phtml')->toHtml();
            $result["status"] = 'success';
        } catch (\Exception $e) {
            $result["status"]  = 'error';
            $result["message"] = $e->getMessage();
        }
        $this->getResponse()->setBody(json_encode($result));
    }
}
