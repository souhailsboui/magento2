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

namespace Mageplaza\StoreCredit\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Mageplaza\StoreCredit\Helper\Data as DataHelper;

/**
 * Class Balance
 * @package Mageplaza\StoreCredit\Block\Adminhtml\Customer\Edit\Tab
 */
class Balance extends Template
{
    /**
     * @var DataHelper
     */
    protected $_helper;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var float Customer balance
     */
    protected $_balance = 0;

    /**
     * Balance constructor.
     *
     * @param Template\Context $context
     * @param DataHelper $dataHelper
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DataHelper $dataHelper,
        Registry $registry,
        array $data = []
    ) {
        $this->_helper = $dataHelper;
        $this->_coreRegistry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * Get Customer Id
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return float
     */
    public function getFormattedBalance()
    {
        return $this->_helper->getAccountHelper()->getFormattedBalance($this->getCustomerId());
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        if (!$this->_balance) {
            $this->_balance = $this->_helper->getAccountHelper()->getBalance($this->getCustomerId());
        }

        return $this->_balance;
    }

    /**
     * @return bool
     */
    public function isCreditNotification()
    {
        $customer = $this->_helper->getAccountHelper()->getCustomerById($this->getCustomerId());

        return (bool)$customer->getData('mp_credit_notification');
    }

    /**
     * @return string
     */
    public function getChangeAmountUrl()
    {
        return $this->getUrl('mpstorecredit/customer/change', ['isAjax' => true]);
    }
}
