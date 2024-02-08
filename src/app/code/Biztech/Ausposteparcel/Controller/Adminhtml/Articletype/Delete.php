<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Articletype;

class Delete extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $model = $this->_objectManager->get('Biztech\Ausposteparcel\Model\Articletype')->load($id);
            $model->delete();
            $this->messageManager->addSuccess(
                __('Article was successfully deleted!')
            );
            $this->_redirect('*/*/index');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
        $this->messageManager->addError('Unable to find the item to delete');
        $this->_redirect('*/*/index');
        return;
    }
}
