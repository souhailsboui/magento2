<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderAmount
 * @author     Extension Team
 * @copyright  Copyright (c) 2023-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderAmount\Plugin\Model\Checkout\Type;

use Bss\OrderAmount\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Multishipping
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param Data $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Bss\OrderAmount\Helper\Data $data
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->data = $data;
    }

    /**
     * Check if quote amount is allowed for multishipping checkout
     *
     * @return bool
     * @throws LocalizedException
     */
    public function afterValidateMinimumAmount()
    {
        $minimumOrderActive = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $minimumOrderMultiFlag = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/multi_address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($minimumOrderMultiFlag) {
            $result = !($minimumOrderActive && !$this->getQuote()->validateMinimumAmount());
        } else {
            $result = !($minimumOrderActive && !$this->validateMinimumAmountForAddressItems());
        }

        return $result;
    }

    /**
     * Retrieve quote model
     *
     * @return \Magento\Quote\Model\Quote|void
     * @throws LocalizedException
     */
    public function getQuote()
    {
        try {
            return $this->checkoutSession->getQuote();
        } catch (NoSuchEntityException $e) {
        }
    }

    /**
     * Validate minimum amount for "Checkout with Multiple Addresses" when
     * "Validate Each Address Separately in Multi-address Checkout" is No.
     *
     * @return bool
     * @throws LocalizedException
     */
    private function validateMinimumAmountForAddressItems()
    {
        $result = true;
        $storeId = $this->getQuote()->getStoreId();
        $minAmount = $this->data->getMinAmount();
        $taxInclude = $this->scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $this->getQuote()->collectTotals();
        $addresses = $this->getQuote()->getAllAddresses();

        $baseTotal = 0;
        foreach ($addresses as $address) {
            $taxes = $taxInclude
                ? $address->getBaseTaxAmount() + $address->getBaseDiscountTaxCompensationAmount()
                : 0;
            $baseTotal += $address->getBaseSubtotalWithDiscount() + $taxes;
        }

        if ($baseTotal < $minAmount) {
            $result = false;
        }
        return $result;
    }
}
