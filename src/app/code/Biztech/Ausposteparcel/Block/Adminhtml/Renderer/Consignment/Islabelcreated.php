<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment;

class Islabelcreated extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $orderRepository;
    protected $_storeManager;
    protected $_assetRepo;
    protected $urlinterface;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Model\UrlInterface $urlinterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order $order
    ) {
        $this->urlinterface = $urlinterface;
        $this->_assetRepo = $assetRepo;
        $this->_storeManager = $storeManager;
        $this->order = $order;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData('consignment_number');
        if (!$value) {
            $html = '';
        } else {
            if($row->getData('is_label_generated')!=1 && $row->getData('is_label_generated')!="1") {
                $orderdata = $this->order->load($row->getData('order_id'));
                $orderdata->setData('is_label_generated',"0");
                $orderdata->save();
            }
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            if ($row->getData('is_label_generated')) {
                $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/icon-enabled.png");
                //$imgLink = $mediaUrl . "ausposteParcel/images/icon-enabled.png";
                $html = '<img title="Label Created" src="' . $imgLink . '" />';
            } else {
                //$imgLink = $mediaUrl . "ausposteParcel/images/cancel_icon.gif";
                $imgLink = $this->_assetRepo->getUrl("Biztech_Ausposteparcel::ausposteParcel/images/cancel_icon.gif");
                //$link = $this->urlinterface->getUrl('ausposteparcel/consignment/massGenerateAndDownloadLabels', array('order_consignment' => $row->getData('order_consignment')));
                $html = '<img title="Label Not Created" src="' . $imgLink . '" />';
                //$html = '<a href="' . $link . '" border="0">' . $image . '</a>';
            }
        }
        return $html;
    }
}
