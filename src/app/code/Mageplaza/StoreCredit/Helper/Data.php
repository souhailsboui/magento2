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

namespace Mageplaza\StoreCredit\Helper;

use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\StoreCredit\Model\ResourceModel\Customer\Collection;
use Mageplaza\StoreCredit\Model\Transaction;

/**
 * Class Data
 * @package Mageplaza\StoreCredit\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpstorecredit';
    /**
     * Transaction Action
     */
    const ACTION_ADMIN_UPDATE = 'admin_update';
    const ACTION_EARNING_ORDER = 'earning_order';
    const ACTION_EARNING_REFUND = 'earning_refund';
    const ACTION_SPENDING_ORDER = 'spending_order';
    const ACTION_SPENDING_REFUND = 'spending_refund';
    const ACTION_REVERT = 'revert';
    const ACTION_EARNING_AFFILIATE = 'earning_affiliate';

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Quote|Session
     */
    protected $checkoutSession;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var JoinProcessorInterface
     */
    protected $joinProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var PackageInfoFactory
     */
    protected $packageInfo;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $joinProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PackageInfoFactory $packageInfo
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $joinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PackageInfoFactory $packageInfo
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->collectionProcessor = $collectionProcessor;
        $this->joinProcessor = $joinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->packageInfo = $packageInfo;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $amount
     * @param bool $includeContainer
     * @param null $scope
     * @param null $currency
     * @param int $precision
     *
     * @return float
     */
    public function formatPrice(
        $amount,
        $includeContainer = true,
        $scope = null,
        $currency = null,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->priceCurrency->format($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * @param $amount
     * @param bool $includeContainer
     * @param null $scope
     * @param null $currency
     * @param int $precision
     *
     * @return float
     */
    public function convertAndFormatPrice(
        $amount,
        $includeContainer = true,
        $scope = null,
        $currency = null,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->priceCurrency->convertAndFormat($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * @param $amount
     * @param bool $format
     * @param bool $includeContainer
     * @param null $scope
     *
     * @return float|string
     */
    public function convertPrice($amount, $format = true, $includeContainer = true, $scope = null)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat(
                $amount,
                $includeContainer,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $scope
            )
            : $this->priceCurrency->convert($amount, $scope);
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->objectManager->create(Transaction::class);
    }

    /**
     * @return Account
     */
    public function getAccountHelper()
    {
        return $this->objectManager->get(Account::class);
    }

    /**
     * @return Email
     */
    public function getEmailHelper()
    {
        return $this->objectManager->get(Email::class);
    }

    /**
     * Check the current page is OSC
     *
     * @return bool
     */
    public function isOscPage()
    {
        $moduleEnable = $this->isModuleOutputEnabled('Mageplaza_Osc');
        $isOscModule = ($this->_request->getRouteName() === 'onestepcheckout');

        return $moduleEnable && $isOscModule;
    }

    /**
     * @param null $customerId
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabledForCustomer($customerId = null, $storeId = null)
    {
        if ($customer = $this->getAccountHelper()->getCustomerById($customerId)) {
            return in_array($customer->getGroupId(), $this->getEnabledForCustomerGroups($storeId));
        }

        return false;
    }

    /**
     * @param $action
     * @param $customer
     * @param $amount
     * @param Order $order
     * @param int $qty
     *
     * @throws LocalizedException
     */
    public function addTransaction($action, $customer, $amount, $order, $qty = 1)
    {
        if (is_numeric($customer)) {
            $customer = $this->getAccountHelper()->getCustomerById($customer);
        }

        if ($customer) {
            $this->getTransaction()->createTransaction(
                $action,
                $customer,
                new DataObject([
                    'amount' => $amount * $qty,
                    'order_id' => $order->getId(),
                    'increment_id' => $order->getIncrementId()
                ])
            );
        }
    }

    /**
     * Get checkout session for admin and frontend
     *
     * @return Quote|Session
     */
    public function getCheckoutSession()
    {
        if (!$this->checkoutSession) {
            $this->checkoutSession = $this->objectManager->get($this->isAdmin() ? Quote::class : Session::class);
        }

        return $this->checkoutSession;
    }

    /**
     * Get Store Credit module version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->packageInfo->create()->getVersion($this->_getModuleName());
    }

    /**
     * ======================================= General Configuration ===================================================
     *
     * @param null $storeId
     *
     * @return array
     */
    public function getEnabledForCustomerGroups($storeId = null)
    {
        return explode(',', $this->getConfigGeneral('customer_groups', $storeId));
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isDisplayedOnToplink($storeId = null)
    {
        return $this->isEnabled($storeId)
            && $this->getAccountHelper()->isCustomerLoggedIn()
            && $this->getConfigGeneral('top_link', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isAllowRefundExchange($storeId = null)
    {
        return !!$this->getConfigGeneral('allow_refund_exchange', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isAllowRefundProduct($storeId = null)
    {
        return !!$this->getConfigGeneral('allow_refund_product', $storeId);
    }

    /**
     * @param Collection $searchResult
     * @param $searchCriteria
     *
     * @return mixed
     */
    public function processGetList($searchResult, $searchCriteria = null)
    {
        if ($searchCriteria === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
