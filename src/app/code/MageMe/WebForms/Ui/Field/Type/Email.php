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

namespace MageMe\WebForms\Ui\Field\Type;


use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use MageMe\WebForms\Config\Options\CustomerDataOptions;
use MageMe\WebForms\Config\Options\Field\CaseTransform;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\BodyTmpl;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Ui\Component\Form;

class Email extends Text implements FieldResultListingColumnInterface
{
    const IS_FILLED_BY_CUSTOMER_EMAIL = Type\Email::IS_FILLED_BY_CUSTOMER_EMAIL;
    const IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL = Type\Email::IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL;
    const MATCH_VALUE_FIELD_ID = Type\Email::MATCH_VALUE_FIELD_ID;

    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param Registry $registry
     * @param CaseTransform $caseTransform
     * @param CustomerDataOptions $customerDataOptions
     */
    public function __construct(
        RequestInterface    $request,
        Registry            $registry,
        CaseTransform $caseTransform,
        CustomerDataOptions $customerDataOptions)
    {
        parent::__construct($caseTransform, $customerDataOptions);
        $this->registry = $registry;
        $this->request  = $request;
    }

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::PLACEHOLDER => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::PLACEHOLDER,
                                    'visible' => 0,
                                    'sortOrder' => 45,
                                    'label' => __('Placeholder'),
                                    'additionalInfo' => __('Placeholder text will appear in the input and disappear on the focus'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MASK => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::MASK,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Input Mask'),
                                    'additionalInfo' => __('<i>9</i> : numeric<br><i>a</i> : alphabetical<br><i>*</i> : alphanumeric<br><i>{min,max}</i> : how many times can a symbol be repeated, <i>min</i> and <i>max</i> are integer numbers<br><i>[***]</i> : optional part of the mask, hidden by default.'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::CASE_TRANSFORM => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::CASE_TRANSFORM,
                                    'visible' => 0,
                                    'sortOrder' => 66,
                                    'label' => __('Transform text after save'),
                                    'options' => $this->caseTransform->toOptionArray(),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_FILLED_BY_CUSTOMER_EMAIL => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_FILLED_BY_CUSTOMER_EMAIL,
                                    'visible' => 0,
                                    'sortOrder' => 67,
                                    'label' => __('Pre-fill With The Customer Email'),
                                    'additionalInfo' => __('Pre-fill data for registered customer with customer\'s e-mail address'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL,
                                    'visible' => 0,
                                    'sortOrder' => 68,
                                    'label' => __('Assign Customer ID Automatically'),
                                    'additionalInfo' => __('Assign Customer ID automatically if e-mail address matches customer account in the database'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MATCH_VALUE_FIELD_ID => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::MATCH_VALUE_FIELD_ID,
                                    'visible' => 0,
                                    'sortOrder' => 69,
                                    'label' => __('Match Value With Another Field'),
                                    'options' => $this->getMatchFieldsAsOptions(),
                                    'caption' => __('-- Please Select --'),
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getMatchFieldsAsOptions(): array
    {
        $options = [];
        $id = $this->request->getParam(FieldInterface::ID);
        try {

            /** @var FormInterface $form */
            $form = $this->registry->registry('webforms_form');
            $options = $form->getFieldsAsOptions(Type\Email::class, ['ne_field_ids' => [$id]]);
        } catch (Exception $exception) {
        }
        return $options;
    }

    /**
     * @inheritDoc
     */
    public function getResultListingColumnConfig(int $sortOrder): array
    {
        $config                  = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['class']         = \MageMe\WebForms\Ui\Component\Result\Listing\Column\Field\Email::class;
        $config['bodyTmpl']      = BodyTmpl::HTML;
        $config['disableAction'] = true;
        return $config;
    }
}
