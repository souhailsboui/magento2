<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebFormsCustomerRegistration\Ui\Component\Form\Form\Modifier;


use MageMe\WebForms\Config\Options\EmailTemplate;
use MageMe\WebFormsCustomerRegistration\Api\Data\FormInterface;
use MageMe\WebFormsCustomerRegistration\Helper\Data;
use Magento\Customer\Model\Customer\Attribute\Source\Group;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Ui\Component\Form;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class CustomerRegistrationSettings implements ModifierInterface
{
    /**
     * @var EmailTemplate
     */
    protected $group;

    /**
     * @var array
     */
    protected $groupOptions;

    /**
     * @var array
     */
    protected $fieldMapOptions;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $crMapValue;

    /**
     * @param Group $group
     * @param Registry $registry
     * @param Data $helper
     * @throws LocalizedException
     */
    public function __construct(
        Group    $group,
        Registry $registry,
        Data     $helper
    ) {
        $this->group        = $group;
        $this->groupOptions = $group->toOptionArray();

        $fieldMapOptions = [['label' => '...', 'value' => '']];

        /** @var FormInterface $form */
        $form = $registry->registry('webforms_form');
        foreach ($form->getFieldsToFieldsets(true) as $fsId => $fieldset) {
            if ($fsId) {
                if ($fieldset['fields']) {
                    $fieldOptions = [];
                    foreach ($fieldset['fields'] as $field) {
                        $fieldOptions[] = [
                            'label' => $field->getName(),
                            'value' => $field->getId(),
                        ];
                    }
                    $fieldMapOptions[] = [
                        'label' => $fieldset['name'],
                        'value' => $fieldOptions,
                    ];
                }
            } else {
                if ($fieldset['fields']) {
                    foreach ($fieldset['fields'] as $field) {
                        $fieldMapOptions[] = [
                            'label' => $field->getName(),
                            'value' => $field->getId(),
                        ];
                    }
                }
            }
        }
        $this->fieldMapOptions = $fieldMapOptions;
        $this->crMapValue      = $form->getCrMap();
        $this->helper          = $helper;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        $meta['customer_registration_settings'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Customer Registration Settings'),
                        'sortOrder' => 120,
                        'collapsible' => true,
                        'opened' => false,
                    ]
                ]
            ],
            'children' => [
                'default_registration_settings' => $this->getDefaultRegistrationSettings(),
                'registration_approval_settings' => $this->getRegistrationApprovalSettings(),
                'registration_email_settings' => $this->getRegistrationEmailSettings(),
                'customer_attribute_mapping' =>
                    $this->getAttributeMapping(
                        'Customer Attributes Mapping', 40,
                        $this->helper->getCustomerAttributes(), 'customer'),
                'billing_address_attribute_mapping' =>
                    $this->getAttributeMapping(
                        'Billing Address Attributes Mapping', 50,
                        $this->helper->getBillingAddressAttributes(), 'billing'),
                'shipping_address_attribute_mapping' =>
                    $this->getAttributeMapping(
                        'Shipping Address Attributes Mapping', 60,
                        $this->helper->getShippingAddressAttributes(), 'shipping'),
                'additional_attribute_mapping' =>
                    $this->getAttributeMapping(
                        'Additional Attributes Mapping', 70,
                        $this->helper->getAdditionalAttributes(), 'additional'),
                'custom_attribute_mapping' => $this->getCustomAttributeMapping(),
            ]
        ];
        return $meta;
    }

    /**
     * @return array
     */
    public function getDefaultRegistrationSettings(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Default Registration Settings'),
                        'sortOrder' => 10,
                        'collapsible' => false,
                    ]
                ]
            ],
            'children' => [
                FormInterface::CR_IS_REGISTERED_ON_SUBMISSION => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                'visible' => 1,
                                'sortOrder' => 10,
                                'label' => __('Register Customer on Form Submission'),
                                'prefer' => 'toggle',
                                'valueMap' => ['false' => '0', 'true' => '1'],
                                'scopeLabel' => '[GLOBAL]',
                            ]
                        ]
                    ]
                ],
                FormInterface::CR_IS_CUSTOMER_EMAIL_UNIQUE => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                'visible' => 1,
                                'sortOrder' => 20,
                                'label' => __('Unique Customer Email Address'),
                                'additionalInfo' => __('Look for entered email address among existing customers and block submission. Message to recover the password will be displayed'),
                                'default' => '0',
                                'prefer' => 'toggle',
                                'valueMap' => ['false' => '0', 'true' => '1'],
                                'scopeLabel' => '[GLOBAL]',
                            ]
                        ]
                    ]
                ],
                FormInterface::CR_DEFAULT_GROUP_ID => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 30,
                                'label' => __('Customer Group'),
                                'options' => $this->groupOptions,
                            ]
                        ]
                    ]
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public function getRegistrationApprovalSettings(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Approval System Registration Settings'),
                        'sortOrder' => 20,
                        'collapsible' => false,
                    ]
                ]
            ],
            'children' => [
                FormInterface::CR_IS_REGISTERED_ON_APPROVAL => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                'visible' => 1,
                                'sortOrder' => 10,
                                'label' => __('Register Customer on Result Approval'),
                                'additionalInfo' => __('If customer is already registered he will be assigned to a new group'),
                                'prefer' => 'toggle',
                                'valueMap' => ['false' => '0', 'true' => '1'],
                                'scopeLabel' => '[GLOBAL]',
                            ]
                        ]
                    ]
                ],
                FormInterface::CR_APPROVAL_GROUP_ID => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 20,
                                'label' => __('Customer Group'),
                                'options' => $this->groupOptions,
                            ]
                        ]
                    ]
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public function getRegistrationEmailSettings(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Email Settings'),
                        'sortOrder' => 30,
                        'collapsible' => false,
                    ]
                ]
            ],
            'children' => [
                FormInterface::CR_IS_DEFAULT_NOTIFICATION_ENABLED => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                'visible' => 1,
                                'sortOrder' => 10,
                                'label' => __('Send Customer Default Magento Notification'),
                                'additionalInfo' => __('You can use the form notifications instead of default Magento new account notification'),
                                'prefer' => 'toggle',
                                'valueMap' => ['false' => '0', 'true' => '1'],
                                'scopeLabel' => '[GLOBAL]',
                            ]
                        ]
                    ]
                ],
            ]
        ];
    }

    /**
     * @param string $label
     * @param int $sortOrder
     * @param array $attributes
     * @param string $entity
     * @return array
     */
    public function getAttributeMapping(string $label, int $sortOrder, array $attributes, string $entity): array
    {
        $fieldset['arguments'] = [
            'data' => [
                'config' => [
                    'componentType' => Form\Fieldset::NAME,
                    'label' => __($label),
                    'sortOrder' => $sortOrder,
                    'collapsible' => false,
                ]
            ]
        ];
        $children              = [];
        $sortOrder             = 1;
        foreach ($attributes as $attribute) {
            $note = '';
            if (isset($attribute['note'])) {
                $note = $attribute['note'];
            }

            if (isset($attribute['required']) && $attribute['required'] == true) {
                $attribute['label'] .= " *";
            }

            $children[FormInterface::CR_MAP . '[' . $entity . '][' . $attribute['code'] . ']'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Form\Field::NAME,
                            'formElement' => Form\Element\Select::NAME,
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'visible' => 1,
                            'sortOrder' => $sortOrder * 10,
                            'label' => $attribute['label'],
                            'additionalInfo' => $note,
                            'options' => $this->fieldMapOptions,
                            'value' => $this->crMapValue[$entity][$attribute['code']] ?? '',
                            'scopeLabel' => '[GLOBAL]',
                        ]
                    ]
                ]
            ];
            $sortOrder++;
        }

        $fieldset['children'] = $children;
        return $fieldset;
    }

    /**
     * @return array
     */
    public function getCustomAttributeMapping(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Custom Attributes Mapping'),
                        'sortOrder' => 80,
                        'collapsible' => false,
                    ]
                ]
            ],
            'children' => [
                FormInterface::CR_MAP . '[custom_attribute]' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\MultiSelect::NAME,
                                'visible' => 1,
                                'sortOrder' => 10,
                                'label' => __('Custom Attributes'),
                                'additionalInfo' => __('Map to custom attribute using field Code value'),
                                'options' => $this->fieldMapOptions,
                                'value' => $this->crMapValue['custom_attribute'] ?? '',
                                'scopeLabel' => '[GLOBAL]',
                            ]
                        ]
                    ]
                ],
            ]
        ];
    }
}
