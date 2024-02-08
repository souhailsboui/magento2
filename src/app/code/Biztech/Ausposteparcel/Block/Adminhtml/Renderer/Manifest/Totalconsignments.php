<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest;

class Totalconsignments extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        return $value;
    }
}
