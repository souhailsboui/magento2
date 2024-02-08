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

namespace Mageplaza\ZohoCRM\Model\Source;

use Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider;

/**
 * Class CustomerGroup
 * @package Mageplaza\ZohoCRM\Model\Source
 */
class CustomerGroup extends CustomerGroupsOptionsProvider
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options             = parent::toOptionArray();
        $options[0]['value'] = -1;

        return $options;
    }
}
