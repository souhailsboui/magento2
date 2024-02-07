<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Nonauspost;

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

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');

        $model = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Nonauspost');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Item does not exist.'));
                $this->_redirect('biztech_nonauspost/*');
                return;
            }
        }
        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        $registryObject->register('nonauspost_data', $model);
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()
                ->prepend($model->getId() ? __('Edit Shipping Types ') : __('Assign Shipping Types'));
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Biztech_Ausposteparcel::nonauspost');
    }

    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Biztech_Ausposteparcel::nonauspost::grid')
                ->addBreadcrumb(__('Assign Shipping Types'), __('Assign Shipping Types'))
                ->addBreadcrumb(__('Assign Shipping Types'), __('Assign Shipping Types'));
        return $resultPage;
    }
}
