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

namespace Mageplaza\ZohoCRM\Model\Rule\Condition\Customer;

use Magento\Rule\Model\Condition\Combine as CoreCombine;
use Magento\Rule\Model\Condition\Context;

/**
 * Class Combine
 * @package Mageplaza\ZohoCRM\Model\Rule\Condition\Customer
 */
class Combine extends CoreCombine
{
    /**
     * @var Condition
     */
    protected $customerCondition;

    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param Condition $customerCondition
     * @param array $data
     */
    public function __construct(
        Context $context,
        Condition $customerCondition,
        array $data = []
    ) {
        $this->customerCondition = $customerCondition;
        parent::__construct($context, $data);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $result = parent::getNewChildSelectOptions();

        $customerAttributes = $this->customerCondition->getNewChildSelectOptions();
        $result[]           = ['value' => __CLASS__, 'label' => __('Conditions Combination')];
        $result[]           = ['value' => $customerAttributes, 'label' => __('Customer')];

        return $result;
    }
}
