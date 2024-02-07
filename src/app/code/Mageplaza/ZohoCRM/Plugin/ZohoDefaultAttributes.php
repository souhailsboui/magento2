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
namespace Mageplaza\ZohoCRM\Plugin;

use Magento\Eav\Model\Entity\AbstractEntity;

/**
 * Class ZohoDefaultAttributes
 * @package Mageplaza\ZohoCRM\Plugin
 */
class ZohoDefaultAttributes
{
    /**
     * @param AbstractEntity $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetDefaultAttributes(AbstractEntity $subject, $result)
    {
        $zohoEntity = [
            'zoho_entity',
            'zoho_lead_entity',
            'zoho_contact_entity'
        ];

        return array_merge($result, $zohoEntity);
    }
}
