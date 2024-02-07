<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Freeshipping;

use Magento\Backend\App\Action\Context;

class Edit extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Freeshipping');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('biztech_freeshipping/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        $registryObject->register('freeshipping_data', $model);
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()
                ->prepend($model->getId() ? __('Edit Freeshipping Rules ') : __('Create Freeshipping Rules'));
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Biztech_Ausposteparcel::freeshipping::grid')
                ->addBreadcrumb(__('Freeshipping Rules Management'), __('Freeshipping Rules Management'))
                ->addBreadcrumb(__('Freeshipping Rules Management'), __('Freeshipping Rules Management'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Biztech_Ausposteparcel::freeshipping');
    }
}
