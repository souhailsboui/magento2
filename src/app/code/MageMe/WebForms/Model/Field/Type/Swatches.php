<?php

namespace MageMe\WebForms\Model\Field\Type;

use MageMe\WebForms\Api\Data\FieldInterface;

class Swatches extends SelectCheckbox
{
    /**
     * Attributes
     */
    const IS_MULTISELECT = 'is_multiselect';
    const IS_INTERNAL_ELEMENTS_VERTICAL = 'is_internal_elements_vertical';
    const SWATCH_WIDTH = 'swatch_width';
    const SWATCH_HEIGHT = 'swatch_height';

    /**
     * @var string
     */
    protected $color_regex = '/{{color (#\w+)}}/';

    #region type attributes

    /**
     * Get multiselect flag
     *
     * @return bool
     */
    public function getIsMultiselect(): bool
    {
        return (bool)$this->getData(self::IS_MULTISELECT);
    }

    /**
     * Set multiselect flag
     *
     * @param bool $isMultiselect
     * @return $this
     */
    public function setIsMultiselect(bool $isMultiselect): Swatches
    {
        return $this->setData(self::IS_MULTISELECT, $isMultiselect);
    }

    /**
     * @return bool
     */
    public function getIsInternalElementsVertical(): bool
    {
        return (bool)$this->getData(self::IS_INTERNAL_ELEMENTS_VERTICAL);
    }

    /**
     * @param bool $isInternalElementsVertical
     * @return $this
     */
    public function setIsInternalElementsVertical(bool $isInternalElementsVertical): Swatches
    {
        return $this->setData(self::IS_INTERNAL_ELEMENTS_VERTICAL, $isInternalElementsVertical);
    }

    /**
     * @return int
     */
    public function getSwatchWidth(): int
    {
        return (int)$this->getData(self::SWATCH_WIDTH);
    }

    /**
     * @param int $maxWidth
     * @return $this
     */
    public function setSwatchWidth(int $maxWidth): Swatches
    {
        return $this->setData(self::SWATCH_WIDTH, $maxWidth);
    }

    /**
     * @return int
     */
    public function getSwatchHeight(): int
    {
        return (int)$this->getData(self::SWATCH_HEIGHT);
    }

    /**
     * @param int $maxHeight
     * @return $this
     */
    public function setSwatchHeight(int $maxHeight): Swatches
    {
        return $this->setData(self::SWATCH_HEIGHT, $maxHeight);
    }

    /**
     * @inheritDoc
     */
    public function getMinOptions(): int
    {
        if (!$this->getIsMultiselect()) {
            return 0;
        }
        return parent::getMinOptions();
    }

    /**
     * @inheritDoc
     */
    public function getMaxOptions(): int
    {
        if (!$this->getIsMultiselect()) {
            return 0;
        }
        return parent::getMaxOptions();
    }

    /**
     * @inheritDoc
     */
    public function getIsInternalElementsInline(): bool
    {
        return false;
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getCssContainerClass(): ?string
    {
        $class = parent::getCssContainerClass();
        return $this->getIsInternalElementsVertical() ? $class : 'inline-elements ' . $class;
    }

    /**
     * @inheritDoc
     */
    public function getCheckedOptionValue(string $value): string
    {
        $value = preg_replace($this->color_regex, "", $value);
        return parent::getCheckedOptionValue($value);
    }

    /**
     * @inheritDoc
     */
    public function getOptionsArray(): array
    {
        $options = parent::getOptionsArray();
        foreach ($options as &$option) {
            $color = false;
            preg_match($this->color_regex, $option['raw_value'], $matches);
            if (!empty($matches[1])) {
                $color = $matches[1];
            }
            $option['color'] = $color;
        }
        return $options;
    }

    /**
     * @inheritDoc
     */
    public function processTypeAttributesOnSave(array &$data, int $storeId): FieldInterface
    {
        if (empty($data[self::OPTIONS])) {
            return $this;
        }
        $oldOptions = $this->getOptions();
        $this->setOptions($data[self::OPTIONS]);
        $options       = $this->getOptionsArray();
        $resultOptions = [];
        foreach ($options as $option) {
            $value = $option['raw_value'];
            if ($option['color'] && empty($option['value'])) {
                $value = "{{val {$option['color']}}} " . $value;
            }
            $resultOptions[] = $value;
        }
        $data[self::OPTIONS] = implode("\n", $resultOptions);
        $this->setOptions($oldOptions);
        return $this;
    }
}