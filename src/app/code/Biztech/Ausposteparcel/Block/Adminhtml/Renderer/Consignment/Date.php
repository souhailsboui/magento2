<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $DateTime;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $DateTime
    ) {
        $this->DateTime = $DateTime;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $date = $row->getData($this->getColumn()->getIndex());
        if ($date) {
            $dateTimestamp = $this->DateTime->timestamp(strtotime($date));
            return date('m/d/Y H:i:s', $dateTimestamp);
        }
    }
}
