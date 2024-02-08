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
 * @package     Mageplaza_StoreCredit
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\StoreCredit\Controller\Adminhtml\Transaction;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Mageplaza\StoreCredit\Controller\Adminhtml\AbstractTransaction;
use Mageplaza\StoreCredit\Helper\Data;

/**
 * Class Save
 * @package Mageplaza\StoreCredit\Controller\Adminhtml\Transaction
 */
class Save extends AbstractTransaction
{
    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPost('transaction')) {
            $customer = $this->helperData->getAccountHelper()->getCustomerById($data['customer_id_form']);
            if (!$customer->getId()) {
                $this->messageManager->addErrorMessage(__('Customer does not exist.'));

                return $this->_redirect('*/*/');
            }

            try {
                $transaction = $this->helperData->getTransaction()
                    ->createTransaction(Data::ACTION_ADMIN_UPDATE, $customer, new DataObject($data));

                $this->messageManager->addSuccessMessage(__('The transaction has been created successfully.'));

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/view', ['id' => $transaction->getId()]);
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }
        }

        return $this->_redirect('*/*/');
    }
}
