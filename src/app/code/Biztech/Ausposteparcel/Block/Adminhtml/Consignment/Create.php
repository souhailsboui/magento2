<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Consignment;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Create extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Biztech\AusposteParcel\Model\ConsignmentFactory
     */
    protected $ausposteParcelConsignmentFactory;
    public $order;
    protected $ordreModel;
    protected $product;
    protected $ausposteParcelInfoHelper;
    protected $_storeManager;
    protected $price;
    protected $articlecollection;
    protected $directoryList;
    protected $scopeConfig;
    public $ausposteParcelCarrierPackFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context     $contex
     * @param \Magento\Sales\Api\Data\OrderInterface    $order
     * @param \Biztech\Ausposteparcel\Model\Consignment $ausposteParcelConsignmentFactory
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $contex,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Sales\Model\Order $ordreModel,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Pricing\Helper\Data $price,
        \Biztech\Ausposteparcel\Model\Carrier\PackFactory $ausposteParcelCarrierPackFactory,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper,
        \Biztech\Ausposteparcel\Model\Cresource\Articletype\Collection $articlecollection,
        \Biztech\Ausposteparcel\Model\Consignment $ausposteParcelConsignmentFactory
    ) {
        $this->ausposteParcelConsignmentFactory = $ausposteParcelConsignmentFactory;
        $this->_objectId = 'order_id';
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_controller = 'adminhtml_consignment';
        $this->_mode = 'create';
        $this->scopeConfig = $contex->getScopeConfig();
        $this->directoryList = $directoryList;
        $this->price = $price;
        $this->ausposteParcelCarrierPackFactory = $ausposteParcelCarrierPackFactory;
        $this->articlecollection = $articlecollection;
        $this->product = $product;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->ordreModel = $ordreModel;
        parent::__construct($contex);
        $this->removeButton('delete');
        if($this->getRequest()->getParam('consignment_number')) {
            $this->removeButton('reset');
        }
        $this->removeButton('save');
        $this->order = $order;
        $this->_storeManager = $contex->getStoreManager();
        // $this->setTemplate('ausposteParcel/consignment/view.phtml');
        $this->setTemplate('ausposteParcel/consignment/consignment_view.phtml');
    }

    public function getDimentions()
    {
        $length_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/length_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $width_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/width_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $height_attr = $this->scopeConfig->getValue('carriers/ausposteParcel/height_attribute', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return array("length_attr" => $length_attr,"width_attr" => $width_attr, "height_attr" => $height_attr);
    }
    public function getDefaultWeight()
    {
        return number_format($this->scopeConfig->getValue('carriers/ausposteParcel/default_weight', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), 2, '.', '');
    }

    public function getValue($path)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $storeScope);
    }
    
    public function useDefaultDimentions()
    {
        return (boolean)$this->scopeConfig->getValue('carriers/ausposteParcel/auspost_allow_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
        return array(
            "length_attr_default" => $length_attr,
            "width_attr_default" => $width_attr,
            "height_attr_default"=>$height_attr
        );
    }

    public function getPriceHelper()
    {
        return $this->price;
    }
    public function eparcelInfo()
    {
        return $this->ausposteParcelInfoHelper;
    }
    public function getArticleCollection()
    {
        return $this->articlecollection;
    }
    public function getOrder()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        return $this->order->load($order_id);
    }
    public function getOrderInfo($orderId)
    {
        return $this->ordreModel->load($orderId);
    }
    public function getProductInfo($orderProductId)
    {
        return $this->product->create()->load($orderProductId);
    }
    public function getHeaderText()
    {
        $header = __('Create Consignment for Order #%1', $this->getOrder()->getRealOrderId());
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('biztech_ausposteparcel/consignment/index');
    }

    public function isFormDisplay()
    {
    }

    public function getTotalItems()
    {
        $order = $this->getOrder();
        return $this->ausposteParcelConsignmentFactory->create()->getTotalItems($order);
    }

    public function getNumberOfArticles()
    {
        return trim($this->getRequest()->getParam('number_of_articles'));
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
        $url = $this->getUrl('biztech_ausposteparcel/consignment/labelCreate/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'source' => 'grid'));
        return $url;
    }

    public function getConsignmentReturnLabelCreateUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/returnLabelCreate/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'source' => 'grid'));
        return $url;
    }

    public function getConsignmentDeleteUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/delete/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'source' => 'grid'));
        return $url;
    }

    public function getConsignmentEditUrl($consignmentNumber, $submittedConsignment = null)
    {
        if (isset($submittedConsignment)) {
            $url = $this->getUrl('biztech_ausposteparcel/consignment/edit/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'submittedConsignment' => $submittedConsignment, 'source' => 'grid'));
        } else {
            $url = $this->getUrl('biztech_ausposteparcel/consignment/edit/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'source' => 'grid'));
        }
        return $url;
    }

    public function getArticleDeleteUrl($consignmentNumber, $articleNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/deleteArticle/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'article_number' => $articleNumber, 'source' => 'grid'));
        return $url;
    }

    public function getArticleEditUrl($consignmentNumber, $articleNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/editArticle/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'article_number' => $articleNumber, 'source' => 'grid'));
        return $url;
    }

    public function getArticleAddUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/addArticle/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'source' => 'grid'));
        return $url;
    }

    public function isReturnLabelFileExists($consignmentNumber)
    {
        $filename = $consignmentNumber . '.pdf';
        $filepath = $this->directoryList->getRoot() . 'pub/media/biztech/ausposteParcel/label/returnlabels/' . $filename;
        return file_exists($filepath);
    }

    public function getConsignmentSubmitUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/submitConsignment/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber));
        return $url;
    }

    public function getSubmitUpdatedConsignmentUrl($consignmentNumber)
    {
        $url = $this->getUrl('biztech_ausposteparcel/consignment/submitUpdatedConsignment/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber));
        return $url;
    }

    public function getOrderAddressUrl($orderId)
    {
        $_order = $this->order->load($orderId);
        $url = null;
        if ($_order->getID()) {
            if ($_order->getShippingAddress()) {
                $shippingId = $_order->getShippingAddress()->getEntityId();
                $url = $this->getUrl('sales/order/address/', array('address_id' => $shippingId));
            }
        }
        return $url;
    }
}
