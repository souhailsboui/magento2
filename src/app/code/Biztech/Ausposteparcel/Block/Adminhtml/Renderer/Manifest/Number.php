<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Number extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData('manifest_number');
        if ($value) {
            $link = $this->getUrl('biztech_ausposteparcel/manifestconsignments/index/', array('manifest' => $value));
            $html = '<a href="' . $link . '">' . $value . '</a>';
        } else {
            $html = '';
        }

        return $html;
    }
}
