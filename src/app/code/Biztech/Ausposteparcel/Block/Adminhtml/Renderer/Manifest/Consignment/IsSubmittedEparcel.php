<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class IsSubmittedEparcel extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_assetRepo;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->_assetRepo = $assetRepo;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData('despatched');
        if ($value == "0") {
            $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/cancel_icon.gif");
            $html = '<img src="' . $imgLink . '" />';
        } else {
            $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/icon-enabled.png");
            $html = '<img src="' . $imgLink . '" />';
        }
        return $html;
    }
}
