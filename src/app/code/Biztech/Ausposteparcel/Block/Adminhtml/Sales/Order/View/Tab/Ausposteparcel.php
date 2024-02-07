<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Sales\Order\View\Tab;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ProductFactory;

class Ausposteparcel extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Biztech_Ausposteparcel::ausposteParcel/consignment/consignment_view.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    protected $scopeConfig;
    protected $order;
    protected $article;
    protected $_filesystem;
    protected $ausposteParcelInfoHelper;
    protected $articlecollection;
    protected $product;
    protected $price;
    public $ausposteParcelCarrierPackFactory;
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order $order,
        \Biztech\Ausposteparcel\Model\Cresource\Articletype\Collection $articlecollection,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper,
        \Biztech\Ausposteparcel\Model\Carrier\PackFactory $ausposteParcelCarrierPackFactory,
        \Biztech\Ausposteparcel\Model\Articletype $article,
        \Magento\Framework\Pricing\Helper\Data $price,
        \Biztech\Ausposteparcel\Helper\Data $helper,
        ProductFactory $product,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->order = $order;
        $this->articlecollection = $articlecollection;
        $this->article = $article;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_filesystem = $context->getFilesystem();
        $this->product = $product;
        $this->ausposteParcelCarrierPackFactory = $ausposteParcelCarrierPackFactory;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->price = $price;
        $this->helper = $helper;
        parent::__construct($context, $data);
        $this->_storeManager = $context->getStoreManager();
    }

    public function getDimentions()
    {
        $length_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/length_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $width_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/width_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $height_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/height_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return ["length_attr" => $length_attr,"width_attr" => $width_attr , "height_attr" => $height_attr];
    }
    public function getDefaultWeight()
    {
        return number_format($this->scopeConfig->getValue('carriers/ausposteParcel/default_weight', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), 2, '.', '');
    }
    public function useDefaultDimentions()
    {
        return (boolean) $this->scopeConfig->getValue('carriers/ausposteParcel/auspost_allow_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function createbox($boxes1)
    {
        $lp = $this->ausposteParcelCarrierPackFactory->create();
        $lp->pack($boxes1);
        $c_size = $lp->get_container_dimensions();
        return $c_size;
    }
    public function useDefaultDimentionsvalue()
    {
        $length_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/length_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $width_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/width_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $height_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/height_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return [
            "length_attr_default" => $length_attr,
            "width_attr_default" => $width_attr,
            "height_attr_default"=>$height_attr
             ];
    }

    public function getConsignments($orderID)
    {
        return $this->ausposteParcelInfoHelper->getConsignments($orderID);
    }

    public function getPriceHelper()
    {
        return $this->price;
    }

    public function getProductInfo($productId)
    {
        return $this->product->create()->load($productId);
    }
    public function getOrderInfo($orderId)
    {
        return $this->order->load($orderId);
    }
    public function getArticleCollection()
    {
        return $this->articlecollection;
    }
    public function getMediaUrl()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    public function getTest()
    {
        return $this->order();
    }

    public function getArticle()
    {
        return $this->article;
    }

    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    public function getTabLabel()
    {
        return __('Parcel Send Consignments');
    }

    public function getTabTitle()
    {
        return __('Parcel Send Consignments');
    }

    public function canShowTab()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $active = $this->scopeConfig->getValue('carriers/ausposteParcel/active', $storeScope);
        if ($active) {
            $orderId = $this->getRequest()->getParam('order_id');
            $orderData = $this->getOrderData($orderId);
            if ($orderData->getIsVirtual()) {
                return false;
            }
            $code = $this->ausposteParcelInfoHelper->getOrderCarrier($orderId);
            if ($code == 'ausposteParcel') {
                if ($this->getOrder()->hasShipments()) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function getValue($path)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $storeScope);
    }

    public function isHidden()
    {
        $active = (int) $this->scopeConfig->getValue('carriers/ausposteParcel/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($active == 1 && (in_array($this->getOrder()->getStoreId(), $this->helper->getAllWebsites()))) {
            if ($this->getOrder()->getState() != 'canceled') {
                $code = $this->ausposteParcelInfoHelper->getOrderCarrier($this->getOrder()->getId());
                if ($code == 'ausposteParcel') {
                    return false;
                }
            }
        }
        return false;
    }

    public function eparcelInfo()
    {
        return $this->ausposteParcelInfoHelper;
    }

    public function getConsignmentCreateUrl()
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/create/');
        $url .= 'order_id/' . $this->getOrder()->getId();
        return $url;
    }

    public function getConsignmentLabelUrl()
    {
        $storeUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $storeUrl .= 'biztech/ausposteParcel/label/consignment/';
        return $storeUrl;
    }

    public function getConsignmentReturnLabelUrl()
    {
        $storeUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $storeUrl .= 'biztech/ausposteParcel/label/returnlabels/';
        return $storeUrl;
    }

    public function getConsignmentLabelCreateUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/labelCreate/', ['order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber]);
        return $url;
    }

    public function getConsignmentReturnLabelCreateUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/returnLabelCreate/', ['order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber]);
        return $url;
    }

    public function getConsignmentDeleteUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/delete/', ['order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber]);
        return $url;
    }

    public function getConsignmentEditUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/edit/', ['order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber]);
        return $url;
    }

    public function getArticleDeleteUrl($consignmentNumber, $articleNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/deletearticle/', ['order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'article_number' => $articleNumber]);
        return $url;
    }

    public function getArticleEditUrl($consignmentNumber, $articleNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/editarticle/', ['order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'article_number' => $articleNumber]);
        return $url;
    }

    public function getArticleAddUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/addarticle/', ['order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber]);
        return $url;
    }

    public function isReturnLabelFileExists($consignmentNumber)
    {
        $filename = $consignmentNumber . '.pdf';
        $currentStore = $this->_storeManager->getStore();
        $filepath = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . '/' . 'ausposteParcel' . '/' . 'label' . '/' . 'returnlabels' . '/' . $filename;
        return file_exists($filepath);
    }

    public function getConsignmentSubmitUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/submitconsignment/', ['order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber]);
        return $url;
    }

    public function getOrderData($orderId)
    {
        return $this->order->load($orderId);
    }

    public function getTabClass()
    {
        // I wanted mine to load via AJAX when it's selected
        // That's what this does
        return 'ajax only';
    }

    public function getClass()
    {
        return $this->getTabClass();
    }

    public function getSubmitUpdatedConsignmentUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/submitUpdatedConsignment/', ['order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber]);
        return $url;
    }

    public function getOrderAddressUrl($orderId)
    {
        $_order = $this->order->load($orderId);
        $url = null;
        if ($_order->getID()) {
            if ($_order->getShippingAddress()) {
                $shippingId = $_order->getShippingAddress()->getEntityId();
                $url = $this->getUrl('sales/order/address/', ['address_id' => $shippingId]);
            }
        }
        return $url;
    }
}
