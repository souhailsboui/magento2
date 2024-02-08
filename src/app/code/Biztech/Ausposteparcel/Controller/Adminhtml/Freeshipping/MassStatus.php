<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Freeshipping;

class MassStatus extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('freeshipping');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $row = $this->_objectManager->get('Biztech\Ausposteparcel\Model\Freeshipping')->load($id);
                    $row->setData('status', $this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully updated.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
        return;
    }
}
