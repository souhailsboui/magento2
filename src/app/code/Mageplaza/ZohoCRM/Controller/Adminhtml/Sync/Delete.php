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

/**
 * Class Delete
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Sync
 */
class Delete extends AbstractSync
{
    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $syncModel = $this->_initSync();
        if (!$syncModel || !$syncModel->getId()) {
            $this->messageManager->addErrorMessage(__('Sync rule not found'));

            return $this->_redirect('*/*/');
        }

        try {
            $syncModel->delete();
            $this->messageManager->addSuccessMessage(__('The sync rule has been deleted successfully.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while deleting the sync rule.'));

            return $this->_redirect('*/*/edit', ['id' => $syncModel->getId()]);
        }

        return $this->_redirect('*/*/');
    }
}
