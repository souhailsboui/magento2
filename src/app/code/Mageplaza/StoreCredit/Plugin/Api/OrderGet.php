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

namespace Mageplaza\StoreCredit\Plugin\Api;

use Exception;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Mageplaza\StoreCredit\Model\Order;
use Mageplaza\StoreCredit\Model\OrderFactory;

/**
 * Class OrderGet
 * @package Mageplaza\StoreCredit\Plugin\Api
 */
class OrderGet
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /** @var OrderExtensionFactory */
    protected $orderExtensionFactory;

    /**
     * OrderGet constructor.
     *
     * @param OrderFactory $orderFactory
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultOrder
     *
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getOrderStoreCredit($resultOrder);

        return $resultOrder;
    }

    /**
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    protected function getOrderStoreCredit(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getMpStoreCredit()) {
            return $order;
        }

        try {
            /** @var Order $storeCreditData */
            $storeCreditData = $this->orderFactory->create()->load($order->getEntityId());
        } catch (Exception $e) {
            return $order;
        }

        /** @var OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setMpStoreCredit($storeCreditData);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param Collection $resultOrder
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        Collection $resultOrder
    ) {
        /** @var  $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $resultOrder;
    }
}
