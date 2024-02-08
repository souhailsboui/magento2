<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Nonauspost;

use Biztech\Ausposteparcel\Model\Nonauspost;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * @property  messageManager
 */
class Save extends Action
{
    protected $_articleType;
    protected $_shippingType;

    public function __construct(
        Context $context,
        Nonauspost $articleType
    ) {
        parent::__construct($context);
        $this->_shippingType = $articleType;
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->_shippingType->load($id);
            }
            $this->_shippingType->setData($data);

            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
            $model = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Nonauspost');
            try {
                if ($id) {
                    $this->_shippingType->setId($id);
                }
                $method = $data['method'];
                if ($id) {
                    $model->load($id);
                    //$method = $model->getMethod();

                    if ($method) {
                        $collection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Cresource\Nonauspost\Collection');
                        $collection->getSelect()->where("method = '$method' AND id != '$id'");
                        if (sizeof($collection) > 0) {
                            $this->messageManager->addError(__('For this shipping method, already charge code assigned'));
                            $this->_redirect('*/*/edit', array('id' => $this->_shippingType->getId()));
                            return;
                        }
                    }
                } else {
                    $collection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Cresource\Nonauspost\Collection');
                    $collection->getSelect()->where("method = '$method'");
                    if (sizeof($collection) > 0) {
                        $this->messageManager->addError(__('For this shipping method, already charge code assigned'));
                        $this->_redirect('*/*/edit', array('id' => $this->_shippingType->getId()));
                        return;
                    }
                }

                $this->_shippingType->save();

                if (!$this->_shippingType->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error while saving'));
                }

                $this->messageManager->addSuccess(__('Shipping Type Saved Successfully!'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $this->_shippingType->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                if ($this->_shippingType && $this->_shippingType->getId()) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $this->_shippingType->getId(), '_current' => true]);
                    return $resultRedirect->setPath('*/*/');
                } else {
                    return $resultRedirect->setPath('*/*/');
                }
            }

            return;
        }
        $this->messageManager->addError(__('No data found to save'));
        return $resultRedirect->setPath('*/*/');
    }
}
