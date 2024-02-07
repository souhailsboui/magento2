<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Returnlabelprinted extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_storeManager;
    protected $_assetRepo;
    protected $urlinterface;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\UrlInterface $urlinterface
    ) {
        $this->urlinterface = $urlinterface;
        $this->_assetRepo = $assetRepo;
        $this->_storeManager = $storeManager;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $eparcelShippingId = $row->getData('eparcelShippingId');
        $order = $row->getData('order_consignment');
        $values = explode('_', $order);
        $orderId = $values[0];
        $consignment_number = $row->getData('consignment_number');
        $html = "";

        if (isset($eparcelShippingId) && $eparcelShippingId != '') {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/pdf.png");
            //$imgLink = $mediaUrl . "ausposteParcel/images/pdf.png";
            $imgLinkUrl = $this->urlinterface->getUrl("*/*/returnLabelCreate", array('order_id' => $orderId, 'eparcelShippingId' => $eparcelShippingId, 'consignment_number' => $consignment_number));
            $html = '<a title="Print Return Label" href="' . $imgLinkUrl . '"><img src="' . $imgLink . '"alt="Generate Label" width="25" height="25" border="0" /></a>';
        }
        return $html;
    }
}
