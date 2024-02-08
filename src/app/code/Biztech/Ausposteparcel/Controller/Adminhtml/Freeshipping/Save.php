<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Freeshipping;

use Magento\Backend\App\Action;

class Save extends Action
{
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $model = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Freeshipping');
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                }
                $model->setData($data);

                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                if ($id) {
                    $model->setId($id);
                }

                $from_amount = $data['from_amount'];
                $to_amount = $data['to_amount'];
                $charge_code = $data['charge_code'];

                if (empty($to_amount)) {
                    $tempToAmount = 0;
                } else {
                    $tempToAmount = $to_amount;
                }

                if ($tempToAmount > 0 && $tempToAmount < $from_amount) {
                    $this->messageManager->addError(__('Please add valid rule, To amount should be greater than from amount.'));
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }

                $collection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Cresource\Freeshipping\Collection');

                if ($id) {
                    $collection->getSelect()->where("((from_amount <= $from_amount and to_amount >= $from_amount) or (from_amount <= '$to_amount' and to_amount >= '$to_amount')) and id != $id and charge_code = '$charge_code'");
                } else {
                    $collection->getSelect()->where("((from_amount <= $from_amount and to_amount >= $from_amount) or (from_amount <= '$to_amount' and to_amount >= '$to_amount')) and charge_code = '$charge_code'");
                }

                if (sizeof($collection) > 0) {
                    $this->messageManager->addError(__('Please add valid rule, this rule may falls within other existing rules range'));
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }

                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error while saving'));
                }

                $this->messageManager->addSuccess(__('Rule Saved Successfully!'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
            return;
        }
        $this->messageManager->addError(__('No data found to save'));
        return $resultRedirect->setPath('*/*/');
    }
}
