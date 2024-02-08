<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Controller\Adminhtml\Sync;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\ZohoCRM\Controller\Adminhtml\AbstractSync;
use Mageplaza\ZohoCRM\Helper\Data;

/**
 * Class Save
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Sync
 */
class Save extends AbstractSync
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('sync');
        if ($data) {
            $syncModel = $this->syncFactory->create();

            if (isset($data['id'])) {
                $syncModel->load($data['id']);
            }

            $rule = $this->getRequest()->getParam('rule', []);
            $syncModel->loadPost($rule);
            $data['mapping'] = Data::jsonEncode($data['mapping']);
            $syncModel->addData($data);
            try {
                $syncModel->save();
                $this->messageManager->addSuccessMessage(__('Save rule success!'));
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', ['id' => $syncModel->getId()]);
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while saving the sync. Please try again later.' . $e->getMessage()));
            }
        }

        return $this->_redirect('*/*/');
    }
}
