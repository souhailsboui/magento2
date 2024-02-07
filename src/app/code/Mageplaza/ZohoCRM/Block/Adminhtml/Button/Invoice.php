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
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Block\Adminhtml\Button;

/**
 * Class Invoice
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\Button
 */
class Invoice extends AbstractButton
{
    public function initButton()
    {
        $invoice   = $this->getParentBlock()->getInvoice();
        $invoiceId = $invoice->getId();
        if ($invoiceId && !$invoice->getZohoEntity()) {
            $this->addButton($invoiceId);
        }
    }

    /**
     * @return string
     */
    public function getPathUrl()
    {
        return 'mpzoho/invoice/add';
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getParamUrl($id)
    {
        return ['invoice_id' => $id];
    }
}
