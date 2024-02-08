<?php

namespace MageMe\WebForms\Block\Form\Element\Field\Type;

class Swatches extends SelectCheckbox
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'swatches.phtml';

    /**
     * @return string
     */
    public function getFieldInputType(): string
    {
        return $this->getField()->getIsMultiselect() ? 'checkbox' : 'radio';
    }

    /**
     * @return int
     */
    public function getSwatchWidth(): int
    {
        return $this->getField()->getSwatchWidth();
    }

    /**
     * @return int
     */
    public function getSwatchHeight(): int
    {
        return $this->getField()->getSwatchHeight();
    }

    /**
     * @return string
     */
    public function getSwatchSize(): string {
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

    /**
     * @param array $option
     * @return string
     */
    public function getSpanStyle(array $option): string
    {
        $style = '';
        if (isset($option['color']) && $option['color']) {
            $style .= "background: {$option['color']};";
        }
        $style .= $this->getSwatchSize();
        return $style;
    }
}