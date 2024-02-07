<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Articletype;

class MassStatus extends \Magento\Backend\App\Action
{

    // protected $messageManager;

    public function __construct(\Magento\Backend\App\Action\Context $context)
    {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
    }
    public function execute()
    {
        $ids = $this->getRequest()->getParam('articletype');
        $data = $this->getRequest()->getParams();
        $articleCollection = $this->_objectManager->get('Biztech\Ausposteparcel\Model\Articletype');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $row = $articleCollection->load($id);
                    $row->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully updated', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
}
