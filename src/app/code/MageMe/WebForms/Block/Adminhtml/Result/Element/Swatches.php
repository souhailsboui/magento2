<?php

namespace MageMe\WebForms\Block\Adminhtml\Result\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Swatches extends AbstractElement
{
    const TYPE = 'swatches';

    /**
     * @inheritDoc
     */
    public function getHtmlAttributes()
    {
        return [
            'name',
            'class',
            'style',
            'onclick',
            'onchange',
            'disabled',
            'data-role',
            'data-action'
        ];
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getElementHtml()
    {
        $options = $this->getData(self::TYPE);
        if (!$options) {
            return '';
        }
        $html = '<div class="nested mm-swatches">';
        $values = is_array($this->getValue()) ? $this->getValue() : [];
        foreach ($options as $i => $option) {
            $checked = in_array($option['value'], $values);
            $html .= $this->optionToHtml($option, $i, $checked);
        }
        $html .= '</div>' . $this->getAfterElementHtml();
        return $html;
    }

    /**
     * @param array $option
     * @param $i
     * @param bool $checked
     * @return string
     */
    private function optionToHtml(array $option, $i, bool $checked = false): string
    {
        $attributes = '';
        foreach ($this->getHtmlAttributes() as $attribute) {
            if ($value = $this->getDataUsingMethod($attribute, $option['value'])) {
                $attributes .= " $attribute=\"$value\"";
            }
        }
        $id = $this->getHtmlId() . '_' . $this->_escape($option['value']);
        return sprintf('<div class="admin__field admin__field-option field choice option-%s">
                                    <label id="%s" for="%s" class="%s">
                                        <input id="%s" class="%s" type="%s" value="%s" %s %s/>
                                        <span style="%s">%s</span>
                                    </label>
                                </div>',
            $i,

            // label
            $id . '_label',
            $id,
            'admin__field-label',

            // input
            $id,
            'admin__control-checkbox',
            $this->getInputType(),
            $option['value'],
            $attributes,
            $checked ? 'checked=checked' : '',

            // span
            $this->getSpanStyle($option),
            $option['label']
        );
    }

    /**
     * @return string
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getInputType(): string
    {
        return $this->getIsMultiselect() ? 'checkbox' : 'radio';
    }

    /**
     * @param array $option
     * @return string
     */
    private function getSpanStyle(array $option): string
    {
        $style = '';
        if (isset($option['color']) && $option['color']) {
            $style .= "background: {$option['color']};";
        }
        $style .= $this->getSwatchSize();
        return $style;
    }

    /**
     * @return string
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getSwatchSize(): string
    {
        $size = '';
        if ($width = $this->getSwatchWidth()) {
            $size .= "width: {$width}px;";
            $size .= "max-width: inherit;";
        }
        if ($height = $this->getSwatchHeight()) {
            $size .= "height: {$height}px;";
            $size .= "line-height: {$height}px;";
        }
        return $size;
    }
}