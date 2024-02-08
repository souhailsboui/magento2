<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

class Islabelprinted extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $orderRepository;
    protected $_storeManager;
    protected $_assetRepo;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_assetRepo = $assetRepo;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData('consignment_number');
        $html = '';

        if (!$value) {
            $html = '';
        } else {
            $valid = $row->getData('labelprinted');
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            if ($valid == 1) {
                $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/icon-enabled.png");
                $html = '<img title="Label Printed" src="' . $imgLink . '" />';
            //$imgLink = $mediaUrl . "ausposteParcel/images/icon-enabled.png";
            } else {
                $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/cancel_icon.gif");
                $html = '<img title="Label Not Printed" src="' . $imgLink . '" />';
                //$imgLink = $mediaUrl . "ausposteParcel/images/cancel_icon.gif";
            }
            /*$imgLink = $block->getViewFileUrl('Biztech_Ausposteparcel/ausposteParcel/images/cancel_icon.gif');*/
        }
        return $html;
    }
}
