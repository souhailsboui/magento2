<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Articletype;

class MassDelete extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('articletype');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select rule(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $row = $this->_objectManager->get('Biztech\Ausposteparcel\Model\Articletype')->load($id);
                    $row->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of  %1 record(s) has been deleted', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
