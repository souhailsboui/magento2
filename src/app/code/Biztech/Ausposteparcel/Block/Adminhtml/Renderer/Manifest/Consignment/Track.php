<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Track extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Biztech\AusposteParcel\Model\ConsignmentFactory
     */
    protected $ausposteParcelConsignmentFactory;

    public function __construct(
        \Biztech\Ausposteparcel\Model\Consignment $ausposteParcelConsignmentFactory
    ) {
        $this->ausposteParcelConsignmentFactory = $ausposteParcelConsignmentFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $order_id = $row->getData('order_id');
        $consignmentModel = $this->ausposteParcelConsignmentFactory->load($order_id, 'order_id')->getData();
        $value = $consignmentModel['eparcel_consignment_id'];

        if (isset($value) || $value != '') {
            $html = '<a href="http://auspost.com.au/track/track.html?id=' . $value . '" target="_blank" >Click</a>';
            return $html;
        }
    }
}
