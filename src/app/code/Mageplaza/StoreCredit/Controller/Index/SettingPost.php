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

namespace Mageplaza\StoreCredit\Controller\Index;

use Exception;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Mageplaza\StoreCredit\Api\StoreCreditCustomerRepositoryInterface;
use Mageplaza\StoreCredit\Helper\Data;

/**
 * Class SettingPost
 * @package Mageplaza\StoreCredit\Controller\Index
 */
class SettingPost extends AbstractAccount
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var StoreCreditCustomerRepositoryInterface
     */
    private $storeCreditCustomerRepository;

    /**
     * SettingPost constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param StoreCreditCustomerRepositoryInterface $storeCreditCustomerRepository
     */
    public function __construct(
        Context $context,
        Data $helper,
        StoreCreditCustomerRepositoryInterface $storeCreditCustomerRepository
    ) {
        $this->helper = $helper;
        $this->storeCreditCustomerRepository = $storeCreditCustomerRepository;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $customer = $this->helper->getAccountHelper()->getCurrentCustomer();
        if ($customer) {
            $isReceiveNotification = (bool)$this->getRequest()->getParam('mp_credit_notification');

            try {
                $this->storeCreditCustomerRepository->updateNotification($customer->getId(), $isReceiveNotification);

                $this->messageManager->addSuccessMessage(__('Saved email settings successfully.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something wrong when saving email notifications.'));
            }
        }

        $this->_redirect('*/*/');
    }
}
