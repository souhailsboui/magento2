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

namespace Mageplaza\StoreCredit\Controller\Adminhtml\Customer;

use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Framework\View\Result\Layout;

/**
 * Class StoreCredit
 * @package Mageplaza\StoreCredit\Controller\Adminhtml\Customer
 */
class StoreCredit extends Index
{
    /**
     * Execute
     *
     * @return Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();

        return $this->resultLayoutFactory->create();
    }
}
