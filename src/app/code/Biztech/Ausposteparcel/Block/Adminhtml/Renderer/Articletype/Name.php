<?php
namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Articletype;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Name extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $name = $row->getData($this->getColumn()->getIndex());
        $weight = $row->getData('weight');
        $height = $row->getData('height');
        $width = $row->getData('width');
        $length = $row->getData('length');
        $html = "$name ({$weight}kg - {$height}x{$width}x{$length})";
        return $html;
    }
}
