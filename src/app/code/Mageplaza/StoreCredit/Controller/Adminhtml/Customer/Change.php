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

namespace Mageplaza\StoreCredit\Controller\Adminhtml\Customer;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Mageplaza\StoreCredit\Helper\Data as DataHelper;
use Mageplaza\StoreCredit\Model\Transaction;
use Mageplaza\StoreCredit\Model\TransactionFactory;

/**
 * Class Change
 * @package Mageplaza\StoreCredit\Controller\Adminhtml\Customer
 */
class Change extends Action
{
    /**
     * @type JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @type DataHelper
     */
    protected $_dataHelper;

    /**
     * @type TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * Change constructor.
     *
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param JsonFactory $resultJsonFactory
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        JsonFactory $resultJsonFactory,
        TransactionFactory $transactionFactory
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_transactionFactory = $transactionFactory;

        parent::__construct($context);
    }

    /**
     * Execute - Change Customer balance amount
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = ['error' => true];
        $request = $this->getRequest();
        if ($request->getParam('isAjax')) {
            $accountHelper = $this->_dataHelper->getAccountHelper();
            $customerId = $request->getParam('customer_id');

            $data = $this->getRequest()->getPostValue();

            /** @var Transaction $transaction */
            $transaction = $this->_transactionFactory->create();

            try {
                $transaction->createTransaction(
                    DataHelper::ACTION_ADMIN_UPDATE,
                    $accountHelper->getCustomerById($customerId),
                    new DataObject($data)
                );

                $result = [
                    'error' => false,
                    'balance' => $accountHelper->getBalance($customerId),
                    'balanceFormatted' => $accountHelper->getFormattedBalance($customerId)
                ];
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
            }
        } else {
            $result['message'] = __('An error occur. Please try again later.');
        }

        return $this->_resultJsonFactory->create()->setData($result);
    }
}
