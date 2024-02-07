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

namespace Mageplaza\StoreCredit\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;
use Mageplaza\StoreCredit\Helper\Data;

/**
 * Class StoreCredit
 * @package Mageplaza\StoreCredit\Model\Sales\Pdf
 */
class StoreCredit extends DefaultTotal
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * StoreCredit constructor.
     *
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param Calculation $taxCalculation
     * @param CollectionFactory $ordersFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $total = [];

        if (abs($this->getAmount()) > 0) {
            $total = [
                'amount' => '-' . $this->helper->formatPrice($this->getAmount(), false),
                'label' => __('Store Credit'),
                'font_size' => $this->getFontSize() ?: 7
            ];
        }

        return [$total];
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->getSource()->getMpStoreCreditDiscount();
    }
}
