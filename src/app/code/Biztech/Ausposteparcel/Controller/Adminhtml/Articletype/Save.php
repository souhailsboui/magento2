<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Articletype;

use Biztech\Ausposteparcel\Model\Articletype;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * @property  messageManager
 */
class Save extends Action
{
    protected $_articleType;

    public function __construct(
        Context $context,
        Articletype $articleType
    ) {
        parent::__construct($context);
        $this->_articleType = $articleType;
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
                $this->_articleType->load($id);
            }
            $this->_articleType->setData($data);
            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
            try {
                if ($id) {
                    $this->_articleType->setId($id);
                }
                $this->_articleType->save();
                if (!$this->_articleType->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error while saving'));
                }

                $this->messageManager->addSuccess(__('Article type Saved Successfully!'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $this->_articleType->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                if ($this->_articleType && $this->_articleType->getId()) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $this->_articleType->getId(), '_current' => true]);
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
