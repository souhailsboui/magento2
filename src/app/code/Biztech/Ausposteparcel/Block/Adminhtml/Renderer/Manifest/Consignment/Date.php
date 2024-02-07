<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $Date;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->Date = $date;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $date = $row->getData($this->getColumn()->getIndex());
        if ($date) {
            $dateTimestamp = $this->Date->timestamp(strtotime($date));
            return date('m/d/Y H:i:s', $dateTimestamp);
        }
    }
}
