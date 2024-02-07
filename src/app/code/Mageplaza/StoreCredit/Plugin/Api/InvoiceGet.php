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
use Magento\Sales\Api\Data\InvoiceExtension;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;
use Mageplaza\StoreCredit\Api\Data\InvoiceInterface as StoreCreditInvoiceInterface;
use Mageplaza\StoreCredit\Model\InvoiceFactory;

/**
 * Class InvoiceGet
 * @package Mageplaza\StoreCredit\Plugin\Api
 */
class InvoiceGet
{
    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var InvoiceExtensionFactory
     */
    protected $invoiceExtensionFactory;

    /**
     * InvoiceGet constructor.
     *
     * @param InvoiceFactory $invoiceFactory
     * @param InvoiceExtensionFactory $invoiceExtensionFactory
     */
    public function __construct(
        InvoiceFactory $invoiceFactory,
        InvoiceExtensionFactory $invoiceExtensionFactory
    ) {
        $this->invoiceFactory = $invoiceFactory;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
    }

    /**
     * @param InvoiceRepositoryInterface $subject
     * @param InvoiceInterface $resultInvoice
     *
     * @return InvoiceInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        InvoiceRepositoryInterface $subject,
        InvoiceInterface $resultInvoice
    ) {
        $resultInvoice = $this->getInvoiceStoreCredit($resultInvoice);

        return $resultInvoice;
    }

    /**
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     */
    protected function getInvoiceStoreCredit(InvoiceInterface $invoice)
    {
        $extensionAttributes = $invoice->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getMpStoreCredit()) {
            return $invoice;
        }

        try {
            /** @var StoreCreditInvoiceInterface $storeCreditData */
            $storeCreditData = $this->invoiceFactory->create()->load($invoice->getEntityId());
        } catch (Exception $e) {
            return $invoice;
        }

        /** @var InvoiceExtension $invoiceExtension */
        $invoiceExtension = $extensionAttributes ? $extensionAttributes : $this->invoiceExtensionFactory->create();
        $invoiceExtension->setMpStoreCredit($storeCreditData);
        $invoice->setExtensionAttributes($invoiceExtension);

        return $invoice;
    }

    /**
     * @param InvoiceRepositoryInterface $subject
     * @param Collection $resultInvoice
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        InvoiceRepositoryInterface $subject,
        Collection $resultInvoice
    ) {
        /** @var  $invoice */
        foreach ($resultInvoice->getItems() as $invoice) {
            $this->afterGet($subject, $invoice);
        }

        return $resultInvoice;
    }
}
