<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Labelprint extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Biztech\AusposteParcel\Model\ConsignmentFactory
     */
    protected $ausposteParcelConsignmentFactory;
    protected $_assetRepo;
    protected $backendUrl;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Model\UrlInterface $urlinterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Biztech\Ausposteparcel\Model\Consignment $ausposteParcelConsignmentFactory
    ) {
        $this->backendUrl = $urlinterface;
        $this->_assetRepo = $assetRepo;
        $this->ausposteParcelConsignmentFactory = $ausposteParcelConsignmentFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $order_id = $row->getData('order_id');
        $consignmentModel = $this->ausposteParcelConsignmentFactory->load($order_id, 'order_id')->getData();
        $value = $consignmentModel['label_request_id'];

        $html ="";
        if (isset($value) && $value != '') {
            $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/pdf.png");
            $imgLinkUrl = $this->backendUrl->getUrl("*/consignment/labelCreate", array('order_id' => $order_id));
            $html = '<a href="' . $imgLinkUrl . '"><img src="' . $imgLink . '"alt="Generate Label" width="25" height="25" border="0" /></a>';
        } else {
            $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/pdf.png");
            $imgLinkUrl = $this->backendUrl->getUrl("*/consignment/labelCreate", array('order_id' => $order_id));
            $html = '<a href="' . $imgLinkUrl . '"><img src="' . $imgLink . '"alt="Generate Label" width="25" height="25" border="0" /></a>';
        }
        return $html;
    }
}
