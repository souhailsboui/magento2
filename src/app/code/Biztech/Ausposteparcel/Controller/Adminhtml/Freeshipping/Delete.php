<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Freeshipping;

class Delete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Freeshipping');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Freeshipping Rules was successfully deleted!'));
                $this->_redirect('*/*/index');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        $this->_redirect('*/*/index');
        return;
    }
}
