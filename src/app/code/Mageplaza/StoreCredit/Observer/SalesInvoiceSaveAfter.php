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

namespace Mageplaza\StoreCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Mageplaza\StoreCredit\Helper\Data;
use Mageplaza\StoreCredit\Model\Config\Source\FieldRenderer;
use Mageplaza\StoreCredit\Model\Product\Type\StoreCredit;
use Psr\Log\LoggerInterface;

/**
 * Class SalesInvoiceSaveAfter
 * @package Mageplaza\StoreCredit\Observer
 */
class SalesInvoiceSaveAfter implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SalesInvoiceSaveAfter constructor.
     *
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();

        if (!$this->helper->isEnabled($invoice->getStoreId()) || $invoice->getState() != Invoice::STATE_PAID) {
            return $this;
        }

        /** @var Invoice\Item $item */
        foreach ($invoice->getAllItems() as $item) {
            /** @var Item $orderItem */
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy() || ($orderItem->getProductType() != StoreCredit::TYPE_STORE_CREDIT)) {
                continue;
            }

            try {
                $this->helper->addTransaction(
                    Data::ACTION_EARNING_ORDER,
                    $invoice->getOrder()->getCustomerId(),
                    $orderItem->getProductOptionByCode(FieldRenderer::CREDIT_AMOUNT),
                    $invoice->getOrder(),
                    $item->getQty()
                );
            } catch (LocalizedException $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $this;
    }
}
