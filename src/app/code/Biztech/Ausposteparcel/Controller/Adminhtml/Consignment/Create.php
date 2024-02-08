<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Create extends Action
{
    protected $request;
    protected $messageManager;
    protected $resultPageFactory;
    protected $resultPage;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->messageManager = $context->getMessageManager();
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();
        $this->resultPage->setActiveMenu('Biztech_Ausposteparcel::consignment');
        $this->resultPage->getConfig()->getTitle()->prepend(__('Create Consignment'));

        $data = $this->getRequest()->getParams();
        $number_of_articles = $this->request->getParam('number_of_articles');
        if (isset($number_of_articles)) {
            $number_of_articles = trim($this->getRequest()->getParam('number_of_articles'));
            if (empty($number_of_articles)) {
                $this->messageManager->addError('Enter number of articles');
            } elseif (!is_numeric($number_of_articles)) {
                $this->messageManager->addError('Enter valid number of articles');
                $number_of_articles = "";
            } elseif ($number_of_articles < 1) {
                $this->messageManager->addError('Enter valid number of articles');
                $number_of_articles = "";
            } elseif ($number_of_articles > 100) {
                $this->messageManager->addError('Number of articles can be 1-100 per request');
                $number_of_articles = "";
            }
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
