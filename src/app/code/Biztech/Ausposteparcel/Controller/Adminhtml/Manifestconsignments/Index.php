<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Manifestconsignments;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
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
        $this->resultPage->setActiveMenu('Biztech_Ausposteparcel::manifest');
        $this->resultPage->getConfig()->getTitle()->prepend(__('Auspost Post Parcel Send Manifest Consignments View'));
        return $this->resultPage;
    }
}
