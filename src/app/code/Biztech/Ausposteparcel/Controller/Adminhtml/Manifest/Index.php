<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Manifest;

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
        $this->resultPage->getConfig()->getTitle()->prepend(__('Manifests Management'));
        return $this->resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Biztech_Ausposteparcel::manifest');
    }
}
