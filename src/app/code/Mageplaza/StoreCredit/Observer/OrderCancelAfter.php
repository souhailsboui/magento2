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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Mageplaza\StoreCredit\Helper\Calculation as Helper;
use Mageplaza\StoreCredit\Model\Config\Source\FieldRenderer;
use Mageplaza\StoreCredit\Model\Product\Type\StoreCredit;
use Psr\Log\LoggerInterface;

/**
 * Class OrderCancelAfter
 * @package Mageplaza\StoreCredit\Observer
 */
class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * OrderCancelAfter constructor.
     *
     * @param Helper $helper
     * @param LoggerInterface $logger
     * @param RequestInterface $request
     */
    public function __construct(
        Helper $helper,
        LoggerInterface $logger,
        RequestInterface $request
    ) {
        $this->helper  = $helper;
        $this->logger  = $logger;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order   = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();

        if (!$this->helper->isEnabled($storeId)) {
            return $this;
        }

        foreach ($order->getAllItems() as $orderItem) {
            /** @var Item $orderItem */
            if ($orderItem->isDummy() || ($orderItem->getProductType() !== StoreCredit::TYPE_STORE_CREDIT)) {
                continue;
            }

            if (!$this->helper->isAllowRefundProduct($storeId)) {
                throw new LocalizedException(__('Store Credit products are not allowed to be refunded.'));
            }

            try {
                /** Take back credit when refunding Store Credit products */
                $this->helper->addTransaction(
                    Helper::ACTION_EARNING_REFUND,
                    $order->getCustomerId(),
                    -$orderItem->getProductOptionByCode(FieldRenderer::CREDIT_AMOUNT),
                    $order,
                    $orderItem->getQtyCanceled()
                );
            } catch (LocalizedException $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        /** Refund store credit from order */
        if (($this->helper->isAllowRefundSpending($storeId)) || ($this->helper->isAllowRefundExchange($storeId))
        ) {
            $credit = $order->getMpStoreCreditBaseDiscount();
            if ($credit > 0.0001) {
                try {
                    $this->helper->addTransaction(
                        Helper::ACTION_SPENDING_REFUND,
                        $order->getCustomerId(),
                        $credit,
                        $order
                    );
                } catch (LocalizedException $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
        }

        return $this;
    }
}
