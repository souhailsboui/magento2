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

namespace Mageplaza\StoreCredit\Block\Account;

use Magento\Customer\Model\Customer;
use Magento\Framework\View\Element\Template;
use Mageplaza\StoreCredit\Helper\Data;
use Mageplaza\StoreCredit\Model\Config\Source\Status;

/**
 * Class Dashboard
 * @method setAccount($account)
 * @method Customer getAccount()
 * @package Mageplaza\StoreCredit\Block\Account
 */
class Dashboard extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Dashboard constructor.
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @param $price
     *
     * @return float
     */
    public function convertPrice($price)
    {
        return $this->helper->convertPrice($price, true, false);
    }

    /**
     * @param $status
     *
     * @return string
     */
    public function getStatusLabel($status)
    {
        $statusArray = Status::getOptionArray();
        if (array_key_exists($status, $statusArray)) {
            return $statusArray[$status];
        }

        return '';
    }
}
