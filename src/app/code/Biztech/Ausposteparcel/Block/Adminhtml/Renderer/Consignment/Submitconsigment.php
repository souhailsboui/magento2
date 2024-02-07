<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Submitconsigment extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_storeManager;
    protected $_assetRepo;
    protected $urlinterface;
    protected $ausposteParcelInfoHelper;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\UrlInterface $urlinterface,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper
    ) {
        $this->urlinterface = $urlinterface;
        $this->_assetRepo = $assetRepo;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->_storeManager = $storeManager;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $order = $row->getData('order_consignment');
        $values = explode('_', $order);
        $orderId = $values[0];
        $consignment_number = $row->getData('consignment_number');
        $eparcelShippingId = $row->getData('eparcelShippingId');
        $isLabelGenerated = $row->getData('is_label_generated');
        $code = $this->ausposteParcelInfoHelper->getOrderCarrier($orderId);

        if ($code != 'ausposteParcel') {
            $html = '<button>' . __('Non Eparcel shipping method.') . '</button>';
            return $html;
        }
        if (isset($consignment_number) && $consignment_number != '' && !isset($eparcelShippingId) || $eparcelShippingId != '') {
            //$html = parent::render($row); //TODO check in M1
            $url = $this->urlinterface->getUrl("*/*/submitConsignment", array('order_id' => $orderId, 'consignment_number' => $row->getConsignmentNumber()));
            $html = '<a title="Download Label" href="' . $this->urlinterface->getUrl("*/*/submitConsignment", array('order_id' => $orderId, 'consignment_number' => $row->getConsignmentNumber())) . '"><button>' . __('Submit Consignment') . '</button></a>';
        }
        if (isset($eparcelShippingId) && $eparcelShippingId != '') {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/pdf.png");
            //$imgLink = $mediaUrl . "ausposteParcel/images/pdf.png";
            $imgLinkUrl = $this->urlinterface->getUrl("*/*/labelCreate", array('order_id' => $orderId));
            $html = '<a title="Download Label" href="' . $imgLinkUrl . '"><img src="' . $imgLink . '"alt="Generate Label" width="25" height="25" border="0" /></a>';
        }
        return $html;
    }
}
