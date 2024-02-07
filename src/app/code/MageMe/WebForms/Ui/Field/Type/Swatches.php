<?php

namespace MageMe\WebForms\Ui\Field\Type;

use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\Field\Type;
use Magento\Ui\Component\Form;

class Swatches extends SelectCheckbox
{
    const IS_INTERNAL_ELEMENTS_VERTICAL = Type\Swatches::IS_INTERNAL_ELEMENTS_VERTICAL;
    const SWATCH_WIDTH = Type\Swatches::SWATCH_WIDTH;
    const SWATCH_HEIGHT = Type\Swatches::SWATCH_HEIGHT;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::OPTIONS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Textarea::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::OPTIONS,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Options'),
                                    'additionalInfo' => __('Select values should be separated with new line<br>Use <i>^Option Text</i> to check default<br>Use <i>Option Text {{val VALUE}}</i> to set different option value<br>Use <i>{{color #HEXCOLOR}}</i> to set the color of the swatch'),
                                    'rows' => 5,
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MIN_OPTIONS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::MIN_OPTIONS,
                                    'visible' => 0,
                                    'sortOrder' => 66,
                                    'label' => __('Minimum Selected Options'),
                                    'additionalInfo' => __('Minimum allowed options'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MAX_OPTIONS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::MAX_OPTIONS,
                                    'visible' => 0,
                                    'sortOrder' => 67,
                                    'label' => __('Maximum Selected Options'),
                                    'additionalInfo' => __('Maximum allowed options'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MIN_OPTIONS_ERROR_TEXT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::MIN_OPTIONS_ERROR_TEXT,
                                    'visible' => 0,
                                    'sortOrder' => 68,
                                    'label' => __('Minimum Selected Options Error Text'),
                                    'additionalInfo' => __('Minimum allowed options error text'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MAX_OPTIONS_ERROR_TEXT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::MAX_OPTIONS_ERROR_TEXT,
                                    'visible' => 0,
                                    'sortOrder' => 69,
                                    'label' => __('Maximum Selected Options Error Text'),
                                    'additionalInfo' => __('Maximum allowed options error text'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_INTERNAL_ELEMENTS_VERTICAL => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_INTERNAL_ELEMENTS_VERTICAL,
                                    'visible' => 0,
                                    'sortOrder' => 70,
                                    'label' => __('Display Vertical'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_MULTISELECT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_MULTISELECT,
                                    'visible' => 0,
                                    'sortOrder' => 71,
                                    'label' => __('Multiple Selection'),
                                    'additionalInfo' => __('Select multiple values'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::SWATCH_WIDTH => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::SWATCH_WIDTH,
                                    'visible' => 0,
                                    'sortOrder' => 72,
                                    'label' => __('Swatch Width'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::SWATCH_HEIGHT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::SWATCH_HEIGHT,
                                    'visible' => 0,
                                    'sortOrder' => 73,
                                    'label' => __('Swatch Height'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config            = $this->getDefaultResultAdminFormConfig();
        $config['type']    = \MageMe\WebForms\Block\Adminhtml\Result\Element\Swatches::TYPE;
        $config['name']    = 'field[' . $this->getField()->getId() . '][]';
        $config[self::SWATCH_WIDTH]    = $this->getField()->getImagesWidth();
        $config[self::SWATCH_HEIGHT]   = $this->getField()->getImagesHeight();
        $config[self::IS_MULTISELECT] = $this->getField()->getIsMultiselect();
        $config[\MageMe\WebForms\Block\Adminhtml\Result\Element\Swatches::TYPE] = $this->getField()->getOptionsArray();
        return $config;
    }
}
