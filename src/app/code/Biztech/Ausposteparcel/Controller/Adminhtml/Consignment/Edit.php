<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;
    public $order;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->order = $order;
    }

    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();
        $this->resultPage->setActiveMenu('Biztech_Ausposteparcel::consignment');
        $orderId = $this->getRequest()->getParam('order_id');
        $OrderToPrepare = $this->order->load($orderId);
        $increamentId = $OrderToPrepare->getData('increment_id');
        $this->resultPage->getConfig()->getTitle()->prepend(__('Edit Consignment #' . $increamentId));
        return $this->resultPage;
    }
}
