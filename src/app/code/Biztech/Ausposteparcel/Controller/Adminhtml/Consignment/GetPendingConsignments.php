<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class GetPendingConsignments extends Action
{
    protected $resultPageFactory;
    protected $resultPage;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();
        $this->resultPage->getConfig()->getTitle()->prepend((__('Pending Consignments')));
        $editBlock = $this->resultPage->getLayout()->createBlock('Biztech\Ausposteparcel\Block\Adminhtml\Pendingconsignment');
        $this->resultPage->addContent($editBlock);

        return $this->resultPage;
    }
}
