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

use Magento\Customer\Controller\RegistryConstants;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;

/**
 * Class Customer
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\Button
 */
class Customer extends AbstractButton
{
    /**
     * @return \Magento\Customer\Model\Customer|mixed
     */
    public function getModel()
    {
        $id = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);

        return $this->customerFactory->create()->load($id);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $customer = $this->getModel();
        if (!$customer->getId()) {
            return [];
        }

        $fields = [
            ['key' => 'zoho_entity', 'label' => __('Account'), 'type' => ZohoModule::ACCOUNT],
            ['key' => 'zoho_lead_entity', 'label' => __('Lead'), 'type' => ZohoModule::LEAD],
            ['key' => 'zoho_contact_entity', 'label' => __('Contact'), 'type' => ZohoModule::CONTACT]
        ];

        $data = [];
        foreach ($fields as $field) {
            if (!$customer->getData($field['key'])) {
                $field['value'] = $customer->getData($field['key']);
                $data[]         = $field;
            }
        }

        if ($data) {
            $message = __('Are you sure you want to do this?');

            if (count($data) > 1) {
                $options = [];
                $i       = 0;
                foreach ($data as $option) {
                    $url = $this->getActionUrl($customer, $option['type']);

                    $options[] = [
                        'id_hard'    => $option['key'],
                        'label'      => __('As ') . $option['label'],
                        'data_attribute' => [
                            'mage-init' => [
                                'Mageplaza_ZohoCRM/js/customer/button' => [
                                    'url'     => $url,
                                    'default' => $i === 0
                                ]
                            ]
                        ],
                        'sort_order' => 1,
                    ];
                    $i++;
                }

                $data = [
                    'label'      => __('Add To Zoho Queue'),
                    'class_name' => SplitButton::class,
                    'options'    => $options,
                ];
            } else {
                $url = $this->getActionUrl($customer, $data[0]['type']);

                $data = [
                    'label'      => __('Add To Zoho Queue As ') . $data[0]['label'],
                    'class'      => 'add_to_zoho',
                    'on_click'   => "confirmSetLocation('{$message}', '{$url}')",
                    'sort_order' => 1,
                ];
            }
        }

        return $data;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $type
     *
     * @return string
     */
    public function getActionUrl($customer, $type)
    {
        return $this->getUrl(
            $this->getPathUrl() . $type,
            $this->getParamUrl($customer->getId())
        );
    }

    /**
     * @return string
     */
    public function getPathUrl()
    {
        return 'mpzoho/customer/add';
    }
}
