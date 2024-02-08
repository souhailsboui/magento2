<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

class Isvalidaddress extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $orderRepository;
    protected $_storeManager;
    protected $_assetRepo;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_assetRepo = $assetRepo;
        $this->_storeManager = $storeManager;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $valid = $row->getData("is_address_valid");
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if ($valid) {
            $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/icon-enabled.png");
            $html = '<img title="Validated" src="' . $imgLink . '" />';
        //$imgLink = $mediaUrl . "ausposteParcel/images/icon-enabled.png";
        } else {
            $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/cancel_icon.gif");
            $html = '<img title="Not Validated" src="' . $imgLink . '" />';
            //$imgLink = $mediaUrl . "ausposteParcel/images/cancel_icon.gif";
        }
        return $html;
    }
}
