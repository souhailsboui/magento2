<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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
namespace Mageplaza\ZohoCRM\Model\Rule\Condition\Order;

use Magento\Rule\Model\Condition\Combine as CoreCombine;
use Magento\Rule\Model\Condition\Context;

/**
 * Class Combine
 * @package Mageplaza\ZohoCRM\Model\Rule\Condition\Order
 */
class Combine extends CoreCombine
{
    /**
     * @var Condition
     */
    protected $conditionOrder;

    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param Condition $conditionOrder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Condition $conditionOrder,
        array $data = []
    ) {
        $this->conditionOrder = $conditionOrder;
        parent::__construct($context, $data);
        $this->setType(__CLASS__);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $addressAttributes = $this->conditionOrder->loadAttributeOptions()->getAttributeOption();
        $attributes        = [];
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = [
                'value' => Condition::class . '|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();

        return array_merge_recursive(
            $conditions,
            [
                [
                    'value' => __CLASS__,
                    'label' => __('Conditions combination')
                ],
                [
                    'label' => __('Order'),
                    'value' => $attributes
                ]
            ]
        );
    }
}
