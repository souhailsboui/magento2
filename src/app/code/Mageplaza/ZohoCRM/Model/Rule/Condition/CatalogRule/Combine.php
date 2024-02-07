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

namespace Mageplaza\ZohoCRM\Model\Rule\Condition\CatalogRule;

use Magento\Rule\Model\Condition\Combine as CoreCombine;
use Magento\Rule\Model\Condition\Context;

/**
 * Class Combine
 * @package Mageplaza\ZohoCRM\Model\Rule\Condition\CatalogRule
 */
class Combine extends CoreCombine
{
    /**
     * @var Condition
     */
    protected $condition;

    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param Condition $condition
     * @param array $data
     */
    public function __construct(
        Context $context,
        Condition $condition,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->condition = $condition;
        $this->setType(__CLASS__);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $fields     = $this->condition->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($fields as $code => $label) {
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
                    'label' => __('Catalog Rule'),
                    'value' => $attributes
                ]
            ]
        );
    }
}
