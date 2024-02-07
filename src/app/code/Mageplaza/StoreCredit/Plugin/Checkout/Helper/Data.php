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

namespace Mageplaza\StoreCredit\Plugin\Checkout\Helper;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\StoreCredit\Helper\Data as StoreCreditHelper;

/**
 * Class Data
 * @package Mageplaza\StoreCredit\Plugin\Checkout\Helper
 */
class Data
{
    /**
     * @var StoreCreditHelper
     */
    private $helper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * Data constructor.
     *
     * @param StoreCreditHelper $helper
     * @param Session $checkoutSession
     */
    public function __construct(StoreCreditHelper $helper, Session $checkoutSession)
    {
        $this->helper          = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Helper\Data $subject
     * @param $result
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterIsAllowedGuestCheckout(\Magento\Checkout\Helper\Data $subject, $result)
    {
        if (!($quote = $this->checkoutSession->getQuote()) || !$this->helper->isEnabled()) {
            return $result;
        }

        if ($this->helper->isModuleOutputEnabled('Mageplaza_Osc')) {
            /** @var \Mageplaza\Osc\Helper\Data $oscHelper */
            $oscHelper = $this->helper->createObject(\Mageplaza\Osc\Helper\Data::class);
            if (!$oscHelper->isEnabled()) {
                return $result;
            }
        }

        if (!$quote->getItems()) {
            return $result;
        }

        foreach ($quote->getItems() as $item) {
            if ($item->getProductType() === 'mpstorecredit') {
                return false;
            }
        }

        return $result;
    }
}
