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

namespace MageMe\WebFormsZoho\Ui\Component\Form\Form\Modifier;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface as FormInterfaceAlias;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Model\Field\Type\Email;
use MageMe\WebFormsZoho\Api\Data\FormInterface;
use MageMe\WebFormsZoho\Config\Options\Crm\LeadFields;
use MageMe\WebFormsZoho\Config\Options\Crm\LeadOwners;
use MageMe\WebFormsZoho\Config\Options\Crm\LeadSources;
use MageMe\WebFormsZoho\Config\Options\Desk\Channels;
use MageMe\WebFormsZoho\Config\Options\Desk\Classification;
use MageMe\WebFormsZoho\Config\Options\Desk\Contacts;
use MageMe\WebFormsZoho\Config\Options\Desk\Departments;
use MageMe\WebFormsZoho\Config\Options\Desk\Languages;
use MageMe\WebFormsZoho\Config\Options\Desk\Owners;
use MageMe\WebFormsZoho\Config\Options\Desk\Priority;
use MageMe\WebFormsZoho\Config\Options\Desk\Status;
use MageMe\WebFormsZoho\Config\Options\Desk\TicketFields;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ZohoIntegrationSettings implements ModifierInterface
{
    const ZOHO_FIELD_ID = 'zoho_field_id';
    /**
     * @var FormRepositoryInterface
     */
    private $formRepository;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var LeadFields
     */
    private $leadFields;
    /**
     * @var LeadOwners
     */
    private $leadOwners;
    /**
     * @var LeadSources
     */
    private $leadSources;
    /**
     * @var Departments
     */
    private $departments;
    /**
     * @var TicketFields
     */
    private $ticketFields;
    /**
     * @var Contacts
     */
    private $contacts;
    /**
     * @var Languages
     */
    private $languages;
    /**
     * @var Channels
     */
    private $channels;
    /**
     * @var Owners
     */
    private $owners;
    /**
     * @var Classification
     */
    private $classification;
    /**
     * @var Priority
     */
    private $priority;
    /**
     * @var Status
     */
    private $status;

    /**
     * @param Status $status
     * @param Priority $priority
     * @param Classification $classification
     * @param Owners $owners
     * @param Channels $channels
     * @param Languages $languages
     * @param Contacts $contacts
     * @param TicketFields $ticketFields
     * @param Departments $departments
     * @param LeadSources $leadSources
     * @param LeadOwners $leadOwners
     * @param LeadFields $leadFields
     * @param RequestInterface $request
     * @param FormRepositoryInterface $formRepository
     */
    public function __construct(
        Status $status,
        Priority $priority,
        Classification $classification,
        Owners $owners,
        Channels $channels,
        Languages $languages,
        Contacts $contacts,
        TicketFields              $ticketFields,
        Departments              $departments,
        LeadSources $leadSources,
        LeadOwners $leadOwners,
        LeadFields              $leadFields,
        RequestInterface        $request,
        FormRepositoryInterface $formRepository
    )
    {
        $this->formRepository = $formRepository;
        $this->request        = $request;
        $this->leadFields     = $leadFields;
        $this->leadOwners     = $leadOwners;
        $this->leadSources     = $leadSources;
        $this->departments     = $departments;
        $this->ticketFields = $ticketFields;
        $this->contacts = $contacts;
        $this->languages = $languages;
        $this->channels = $channels;
        $this->owners = $owners;
        $this->classification = $classification;
        $this->priority = $priority;
        $this->status = $status;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta): array
    {
        $meta['zoho_crm_integration_settings'] = $this->getCrmFieldsetMeta();
        $meta['zoho_desk_integration_settings'] = $this->getDeskFieldsetMeta();
        return $meta;
    }

    /**
     * @return array
     */
    private function getCrmFieldsetMeta(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Zoho CRM Integration Settings'),
                        'sortOrder' => 170,
                        'collapsible' => true,
                        'opened' => false,
                    ]
                ]
            ],
            'children' => [
                FormInterface::ZOHO_CRM_IS_LEAD_ENABLED => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                'visible' => 1,
                                'sortOrder' => 10,
                                'label' => __('Create CRM Lead'),
                                'default' => '0',
                                'prefer' => 'toggle',
                                'valueMap' => ['false' => '0', 'true' => '1'],
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_CRM_EMAIL_FIELD_ID => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Number::NAME,
                                'visible' => 1,
                                'sortOrder' => 20,
                                'label' => __('Customer Email'),
                                'options' => $this->getFields(Email::class),
                                'caption' => __('Default'),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_CRM_LEAD_OWNER => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 30,
                                'label' => __('Lead Owner'),
                                'options' => $this->leadOwners->toOptionArray(),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_CRM_LEAD_SOURCE => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 40,
                                'label' => __('Lead Source'),
                                'options' => $this->leadSources->toOptionArray(),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_CRM_MAP_FIELDS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => DynamicRows::NAME,
                                'visible' => 1,
                                'sortOrder' => 50,
                                'label' => __('Fields Mapping'),
                            ]
                        ]
                    ],
                    'children' => [
                        'record' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Container::NAME,
                                        'isTemplate' => true,
                                        'is_collection' => true,
                                    ]
                                ]
                            ],
                            'children' => [
                                self::ZOHO_FIELD_ID => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Form\Field::NAME,
                                                'formElement' => Form\Element\Select::NAME,
                                                'dataType' => Form\Element\DataType\Text::NAME,
                                                'visible' => 1,
                                                'sortOrder' => 10,
                                                'label' => __('Zoho CRM Lead Attribute'),
                                                'options' => $this->leadFields->toOptionArray(),
                                                'validation' => [
                                                    'required-entry' => true,
                                                ],
                                            ]
                                        ]
                                    ]
                                ],
                                FieldInterface::ID => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Form\Field::NAME,
                                                'formElement' => Form\Element\Select::NAME,
                                                'dataType' => Form\Element\DataType\Text::NAME,
                                                'visible' => 1,
                                                'sortOrder' => 20,
                                                'label' => __('Field'),
                                                'options' => $this->getFields(),
                                                'validation' => [
                                                    'required-entry' => true,
                                                ],
                                            ]
                                        ]
                                    ]
                                ],
                                ActionDelete::NAME => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => ActionDelete::NAME,
                                                'dataType' => Form\Element\DataType\Text::NAME,
                                                'label' => '',
                                                'sortOrder' => 30,
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ]
                    ]
                ],
            ]
        ];
    }

    private function getDeskFieldsetMeta(): array {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Zoho Desk Integration Settings'),
                        'sortOrder' => 171,
                        'collapsible' => true,
                        'opened' => false,
                    ]
                ]
            ],
            'children' => [
                FormInterface::ZOHO_DESK_IS_TICKET_ENABLED => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                'visible' => 1,
                                'sortOrder' => 10,
                                'label' => __('Create Desk Ticket'),
                                'default' => '0',
                                'prefer' => 'toggle',
                                'valueMap' => ['false' => '0', 'true' => '1'],
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_EMAIL_FIELD_ID => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Number::NAME,
                                'visible' => 1,
                                'sortOrder' => 20,
                                'label' => __('Customer Email'),
                                'options' => $this->getFields(Email::class),
                                'caption' => __('Default'),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_DEPARTMENT_ID => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 30,
                                'label' => __('Department'),
                                'options' => $this->departments->toOptionArray(),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_CONTACT_ID => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 40,
                                'label' => __('Contact'),
                                'options' => $this->contacts->toOptionArray(),
                                'caption' => __('-- Auto --'),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_TICKET_STATUS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 50,
                                'label' => __('Status'),
                                'options' => $this->status->toOptionArray(),
                                'caption' => __('-- Default --'),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_TICKET_OWNER => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 60,
                                'label' => __('Owner'),
                                'options' => $this->owners->toOptionArray(),
                                'caption' => __('-- Unassigned --'),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_TICKET_CHANNEL => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 70,
                                'label' => __('Channel'),
                                'options' => $this->channels->toOptionArray(),
                                'caption' => __('-- Default --'),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_TICKET_CLASSIFICATION => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 80,
                                'label' => __('Classification'),
                                'options' => $this->classification->toOptionArray(),
                                'caption' => __('-- None --'),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_TICKET_PRIORITY => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 90,
                                'label' => __('Priority'),
                                'options' => $this->priority->toOptionArray(),
                                'caption' => __('-- None --'),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_TICKET_LANGUAGE => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'visible' => 1,
                                'sortOrder' => 100,
                                'label' => __('Language'),
                                'options' => $this->languages->toOptionArray(),
                                'caption' => __('-- None --'),
                            ]
                        ]
                    ]
                ],
                FormInterface::ZOHO_DESK_MAP_FIELDS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => DynamicRows::NAME,
                                'visible' => 1,
                                'sortOrder' => 110,
                                'label' => __('Fields Mapping'),
                            ]
                        ]
                    ],
                    'children' => [
                        'record' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Container::NAME,
                                        'isTemplate' => true,
                                        'is_collection' => true,
                                    ]
                                ]
                            ],
                            'children' => [
                                self::ZOHO_FIELD_ID => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Form\Field::NAME,
                                                'formElement' => Form\Element\Select::NAME,
                                                'dataType' => Form\Element\DataType\Text::NAME,
                                                'visible' => 1,
                                                'sortOrder' => 10,
                                                'label' => __('Zoho Desk Ticket Attribute'),
                                                'options' => $this->ticketFields->toOptionArray(),
                                                'validation' => [
                                                    'required-entry' => true,
                                                ],
                                            ]
                                        ]
                                    ]
                                ],
                                FieldInterface::ID => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Form\Field::NAME,
                                                'formElement' => Form\Element\Select::NAME,
                                                'dataType' => Form\Element\DataType\Text::NAME,
                                                'visible' => 1,
                                                'sortOrder' => 20,
                                                'label' => __('Field'),
                                                'options' => $this->getFields(),
                                                'validation' => [
                                                    'required-entry' => true,
                                                ],
                                            ]
                                        ]
                                    ]
                                ],
                                ActionDelete::NAME => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => ActionDelete::NAME,
                                                'dataType' => Form\Element\DataType\Text::NAME,
                                                'label' => '',
                                                'sortOrder' => 30,
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ]
                    ]
                ],
            ]
        ];
    }

    /**
     * @param mixed $type
     * @return array
     */
    private function getFields($type = false): array
    {
        $formId = (int)$this->request->getParam(FormInterfaceAlias::ID);
        if (!$formId) {
            return [];
        }
        try {
            return $this->formRepository->getById($formId)->getFieldsAsOptions($type);
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }
}