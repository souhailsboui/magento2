<?php

namespace Biztech\Ausposteparcel\Helper;

use Magento\Framework\App\ResourceConnection;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Info extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $auspostlabelCollectionFactory;
    protected $scopeConfig;
    protected $consignmentCollectionFactory;
    protected $articleCollectionFactory;
    protected $ausposteParcelApiFactory;
    protected $logger;
    protected $ausposteParcelHelper;
    protected $ausposteParcelAuspostlabelFactory;
    protected $_resource;
    protected $order;
    public $_objectManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Biztech\Ausposteparcel\Model\Cresource\Auspostlabel\Collection $auspostlabelCollectionFactory,
        \Biztech\Ausposteparcel\Model\Cresource\Consignment\CollectionFactory $consignmentCollectionFactory,
        \Biztech\Ausposteparcel\Model\Cresource\Article\CollectionFactory $articleCollectionFactory,
        /* \Biztech\Ausposteparcel\Model\ApiFactory $ausposteParcelApiFactory, */
        \Biztech\Ausposteparcel\Helper\Data $ausposteParcelHelper,
        \Biztech\Ausposteparcel\Model\AuspostlabelFactory $ausposteParcelAuspostlabelFactory,
        ResourceConnection $resource,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->auspostlabelCollectionFactory = $auspostlabelCollectionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->consignmentCollectionFactory = $consignmentCollectionFactory;
        $this->articleCollectionFactory = $articleCollectionFactory;
        /* $this->ausposteParcelApiFactory = $ausposteParcelApiFactory; */
        $this->logger = $context->getLogger();
        $this->ausposteParcelHelper = $ausposteParcelHelper;
        $this->ausposteParcelAuspostlabelFactory = $ausposteParcelAuspostlabelFactory;
        $this->_resource = $resource;
        $this->order = $order;
        $this->_objectManager = $objectManager;
        parent::__construct(
            $context
        );
    }

    public function getChargeCodes()
    {
        //$chargeCodes2 = array('3E03', '7E03', '3E05', '7E05', '3E33', '7E33', '3E35', '7E35', '3E53', '7E53', '3E55', '7E55', '3E83', '7E83', '3E85', '7E85', '2A33', '2A35', '2B33', '2B35', '2C33', '2C35', '2D33', '2D35', '2G33', '2G35', '2H33', '2H35', '2I33', '2I35', '2J33', '2J35', '3B03', '3B05', '3C03', '3C05', '3C33', '3C35', '3C53', '3C55', '3C83', '3C85', '3D03', '3D05', '3D33', '3D35', '3D53', '3D55', '3D83', '3D85', '3H03', '3H05', '3I03', '3I05', '3I33', '3I35', '3I53', '3I55', '3I83', '3I85', '3J03', '3J05', '3J33', '3J35', '3J53', '3J55', '3J83', '3J85', '3K03', '3K05', '3K33', '3K35', '3K53', '3K55', '3K83', '3K85', '4A33', '4A35', '4B33', '4B35', '4C33', '4C35', '4D33', '4D35', '4I33', '4I35', '4J33', '4J35', '7B03', '7B05', '7B33', '7B35', '7B53', '7B55', '7B83', '7B85', '7C03', '7C05', '7C33', '7C35', '7C53', '7C55', '7C83', '7C85', '7D03', '7D05', '7D33', '7D35', '7D53', '7D55', '7D83', '7D85', '7H03', '7H05', '7H33', '7H35', '7H53', '7H55', '7H83', '7H85', '7I03', '7I05', '7I33', '7I35', '7I53', '7I55', '7I83', '7I85', '7J03', '7J05', '7J33', '7J35', '7J53', '7J55', '7J83', '7J85', '7K03', '7K05', '7K33', '7K35', '7K53', '7K55', '7K83', '7K85', '7N33', '7N35', '7N83', '7N85', '7O33', '7O35', '7O83', '7O85', '7P33', '7P35', '7P83', '7P85', '7T33', '7T35', '7T83', '7T85', '7U33', '7U35', '7U83', '7U85', '7V33', '7V35', '7V83', '7V85', '8A33', '8A35', '8B33', '8B35', '8C33', '8C35', '8D33', '8D35', '8G33', '8G35', '8H33', '8H35', '8I33', '8I35', '8J33', '8J35', '9A33', '9A35', '9B33', '9B35', '9C33', '9C35', '9D33', '9D35', '9G33', '9G35', '9H33', '9H35', '9I33', '9I35', '9J33', '9J35');

        $chargeCodes = array();
        $readConnection = $this->_resource->getConnection();
        $table = $this->_resource->getTableName('biztech_ausposteParcel_chargecode');

        $query = "select * from {$table}";
        $chargeCodesArray = $readConnection->fetchAll($query);

        foreach ($chargeCodesArray as $chargeCode) {
            $chargeCodes[] = $chargeCode['charge_code'];
        }
        return $chargeCodes;
    }

    public function getProductCode($chargeCode)
    {
        $chargeCodes1 = array(
            'B1', 'B2', 'B3', 'B4', 'B5', 'B96', 'B97', 'B98', 'D1', 'DE1', 'DE2', 'DE4', 'DE5', 'DE6', 'MED1', 'MED2', 'S1', 'S10', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'SV1', 'SV2', 'W5', 'W6'
        );
        if (in_array($chargeCode, $chargeCodes1)) {
            return 60;
        }

        $chargeCodes2 = array(
            'X1', 'X2', 'X5', 'X6', 'XB1', 'XB2', 'XB3', 'XB4', 'XB5', 'XDE5', 'XW5', 'XW6'
        );
        if (in_array($chargeCode, $chargeCodes2)) {
            return 61;
        }

        $chargeCodes3 = array(
            'U1', 'U2', 'U3', 'U4'
        );
        if (in_array($chargeCode, $chargeCodes3)) {
            return 63;
        }

        $chargeCodes4 = array('CS1', 'CS2', 'CS3', 'CS4', 'CS5', 'CS6', 'CS7', 'CS8');
        if (in_array($chargeCode, $chargeCodes4)) {
            return 62;
        }

        $chargeCodes5 = array('CX1', 'CX2');
        if (in_array($chargeCode, $chargeCodes5)) {
            return 66;
        }

        if ($chargeCode == 'PR') {
            return 65;
        }

        if ($chargeCode == 'XPR') {
            return 66;
        }
    }

    public function getServiceCode($chargeCode, $isSignatureDelivery, $isCashToCollect, $isReturnService, $isPartialDelivery)
    {
        $chargeCodes1 = array(
            'B1', 'B2', 'B3', 'B4', 'B5', 'B96', 'B97', 'B98', 'D1', 'DE1', 'DE2', 'DE4', 'DE5', 'DE6', 'MED1', 'MED2', 'S1', 'S10', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'SV1', 'SV2', 'W5', 'W6', 'X1', 'X2', 'X5', 'X6', 'XB1', 'XB2', 'XB3', 'XB4', 'XB5', 'XDE5', 'XW5', 'XW6'
        );

        $chargeCodes2 = array('3E03', '7E03', '3E05', '7E05', '3E33', '7E33', '3E35', '7E35', '3E53', '7E53', '3E55', '7E55', '3E83', '7E83', '3E85', '7E85', '2A33', '2A35', '2B33', '2B35', '2C33', '2C35', '2D33', '2D35', '2G33', '2G35', '2H33', '2H35', '2I33', '2I35', '2J33', '2J35', '3B03', '3B05', '3C03', '3C05', '3C33', '3C35', '3C53', '3C55', '3C83', '3C85', '3D03', '3D05', '3D33', '3D35', '3D53', '3D55', '3D83', '3D85', '3H03', '3H05', '3I03', '3I05', '3I33', '3I35', '3I53', '3I55', '3I83', '3I85', '3J03', '3J05', '3J33', '3J35', '3J53', '3J55', '3J83', '3J85', '3K03', '3K05', '3K33', '3K35', '3K53', '3K55', '3K83', '3K85', '4A33', '4A35', '4B33', '4B35', '4C33', '4C35', '4D33', '4D35', '4I33', '4I35', '4J33', '4J35', '7B03', '7B05', '7B33', '7B35', '7B53', '7B55', '7B83', '7B85', '7C03', '7C05', '7C33', '7C35', '7C53', '7C55', '7C83', '7C85', '7D03', '7D05', '7D33', '7D35', '7D53', '7D55', '7D83', '7D85', '7H03', '7H05', '7H33', '7H35', '7H53', '7H55', '7H83', '7H85', '7I03', '7I05', '7I33', '7I35', '7I53', '7I55', '7I83', '7I85', '7J03', '7J05', '7J33', '7J35', '7J53', '7J55', '7J83', '7J85', '7K03', '7K05', '7K33', '7K35', '7K53', '7K55', '7K83', '7K85', '7N33', '7N35', '7N83', '7N85', '7O33', '7O35', '7O83', '7O85', '7P33', '7P35', '7P83', '7P85', '7T33', '7T35', '7T83', '7T85', '7U33', '7U35', '7U83', '7U85', '7V33', '7V35', '7V83', '7V85', '8A33', '8A35', '8B33', '8B35', '8C33', '8C35', '8D33', '8D35', '8G33', '8G35', '8H33', '8H35', '8I33', '8I35', '8J33', '8J35', '9A33', '9A35', '9B33', '9B35', '9C33', '9C35', '9D33', '9D35', '9G33', '9G35', '9H33', '9H35', '9I33', '9I35', '9J33', '9J35');

        if (in_array($chargeCode, $chargeCodes1) || in_array($chargeCode, $chargeCodes2)) {
            if (!$isSignatureDelivery && $isPartialDelivery) {
                return 15;
            } else {
                return 38;
            }
        }

        $chargeCodes3 = array(
            'U1', 'U2', 'U3', 'U4'
        );
        if (in_array($chargeCode, $chargeCodes3)) {
            if ($isReturnService) {
                if ($isSignatureDelivery) {
                    return 02;
                } else {
                    return 8;
                }
            } else {
                if ($isSignatureDelivery || $isPartialDelivery) {
                    return 14;
                } elseif (!$isSignatureDelivery && $isPartialDelivery) {
                    return 15;
                }
            }
        }

        $chargeCodes4 = array('CS1', 'CS2', 'CS3', 'CS4', 'CS5', 'CS6', 'CS7', 'CS8', 'CX1', 'CX2');
        if (in_array($chargeCode, $chargeCodes4)) {
            return 17;
        }

        if ($chargeCode == 'PR' || $chargeCode == 'XPR') {
            if ($isReturnService) {
                if ($isSignatureDelivery) {
                    return 02;
                } else {
                    return 8;
                }
            }
        }
    }

    public function getChargeCodeValues($none = false)
    {
        $data = $this->auspostlabelCollectionFactory->getData();
        sort($data);
        $options = array();
        if ($none) {
            $options['none'] = 'None';
        }
        foreach ($data as $chargeCode) {
            $options[$chargeCode['charge_code']] = $chargeCode['type'];
        }
        return $options;
    }

    public function getChargeCodeOptions($none = false)
    {
        $auspostlabelChargeCodes = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Cresource\Auspostlabel\Collection');
        $codes = $auspostlabelChargeCodes->getData();
        $options = array();
        $option = array('value' => '', 'label' => 'Please Select');
        sort($codes);
        $options[] = $option;
        if ($none) {
            $option = array('value' => 'none', 'label' => 'None');
            $options[] = $option;
        }
        foreach ($auspostlabelChargeCodes->getData() as $chargeCode) {
            $option = array('value' => $chargeCode['charge_code'], 'label' => $chargeCode['type']);
            $options[] = $option;
        }
        return $options;
    }

    public function getRegion($region_id)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('directory_country_region');
        $query = "select code from {$tableName} where region_id = '{$region_id}'";
        return $connection->fetchOne($query);
    }

    public function getFreeshipping($charge_code, $price)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('biztech_ausposteParcel_free_shipping');
        $charge_code = trim($charge_code);

        $query = "select * from {$table} where charge_code = '{$charge_code}' and status = 1 order by from_amount";
        $result = $readConnection->fetchAll($query);
        if ($result && count($result) > 0) {
            foreach ($result as $row) {
                if ($price >= $row['from_amount']) {
                    if ($row['to_amount'] > 0) {
                        if ($price <= $row['to_amount']) {
                            return $row;
                        }
                    } else {
                        return $row;
                    }
                }
            }
        }
        return false;
    }

    public function getAllowedWeightPerArticle()
    {
        return 22;
    }

    public function getOrderCarrier($id_order)
    {
        $allowedChargeCodes = $this->getChargeCodes();

        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($id_order);
        $shippingCode = $order->getShippingMethod(true)->getCarrierCode();

        if ($shippingCode != 'ausposteParcel') {
            $method = $order->getShippingDescription();
            $charge_code = $this->getNonausposteParcelShippingTypeChargecode($method);
            if (in_array($charge_code, $allowedChargeCodes)) {
                $shippingCode = 'ausposteParcel';
            } else {
                if ($charge_code == 'none') {
                    $shippingCode = 'none';
                } else {
                    if ($this->scopeConfig->getValue('carriers/ausposteParcel/eParcelShippingApplyAll', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('carriers/ausposteParcel/defaultChargeCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != '') {
                        $shippingCode = 'ausposteParcel';
                    }
                }
            }
        }

        return $shippingCode;
    }
    /**
     * Return shipment generated or not for the order
     *
     * @param  int $orderId
     * @return boolean
     */
    public function isOrderShipped($orderId)
    {
        return (bool) $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId)->hasShipments();
    } 

    public function getShippingMethod($id_order)
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($id_order);
        return $order->getShippingMethod();
    }

    public function getNonausposteParcelShippingTypeChargecode($method)
    {
        $method = trim($method);
        $readConnection = $this->_resource->getConnection();
        $table = $this->_resource->getTableName('biztech_ausposteParcel_nonausposteParcel');
        //For international methods
        if (strpos($method, "'") === false) {
            //$method1 = addslashes($method, "'");
            $method1 = addslashes($method);
            $query = "SELECT charge_code FROM {$table} WHERE method = '" . $method1 . "'";
        } else {
            $method = str_replace("'", "\'", $method);
            $method = str_replace('"', '\"', $method);
            $query = "SELECT charge_code FROM {$table} WHERE method = '" . $method . "'";
            // $query = 'SELECT charge_code FROM '.$table.' WHERE method = "' . $method . '"';
        }
        return $readConnection->fetchOne($query);
    }

    public function isDisablePartialDeliveryMethod($id_order)
    {
        $allowedChargeCodes = array('PR', 'XPR');

        $chargeCode = $this->getOrderChargeCode($id_order);
        if (in_array($chargeCode, $allowedChargeCodes)) {
            return true;
        }
        return false;
    }

    public function getOrderChargeCode($id_order, $consignment_number = '')
    {
        $order = $this->order->load($id_order);
        $charge_code = $order->getShippingMethod(true)->getMethod();
        $allowedChargeCodes = $this->getChargeCodes();

        if (!in_array($charge_code, $allowedChargeCodes)) {
            $method = $order->getShippingDescription();
            $charge_code = $this->getNonausposteParcelShippingTypeChargecode($method);
            if (!in_array($charge_code, $allowedChargeCodes)) {
                if ($this->scopeConfig->getValue('carriers/ausposteParcel/eParcelShippingApplyAll', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('carriers/ausposteParcel/defaultChargeCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != '') {
                    $charge_code = '';
                    if (!empty($consignment_number)) {
                        $consignment = $this->getConsignment($order->getId(), $consignment_number);
                        $charge_code = $consignment['general_ausposteParcel_shipping_chargecode'];
                    }

                    if (empty($charge_code)) {
                        $charge_code = $this->scopeConfig->getValue('carriers/ausposteParcel/defaultChargeCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    }
                }
            }
        }
        return $charge_code;
    }

    public function getConsignment($id_order, $consignment_number)
    {
        $consignment = $this->consignmentCollectionFactory->create();
        $consignment->addFieldToFilter('order_id', $id_order)
            ->addFieldToFilter('consignment_number', $consignment_number);
        $consignmentData = $consignment->getData();
        return $consignmentData[0];
    }

    public function getConsignmentArticleByManifestNumber($manifest_number)
    {
        $consignments = $this->consignmentCollectionFactory->create()->addFieldToFilter('manifest_number', $manifest_number)
            ->getData();

        $articleCount = 0;
        foreach ($consignments as $consignment) {
            $articles = $this->articleCollectionFactory->create()->addFieldToFilter('consignment_number', $consignment['consignment_number'])
                ->getData();
            $articleCount += count($articles);
        }

        return array('numberOfConsignments' => count($consignments), 'numberOfArticles' => $articleCount);
    }

    public function getArticleByConsignmentNumber($consignment_number)
    {
        $articles = $this->articleCollectionFactory->create()->addFieldToFilter('consignment_number', $consignment_number)
            ->getData();
        $articleCount = count($articles);

        return $articleCount;
    }

    public function getConsignments($id_order)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $table = $resource->getTableName('biztech_ausposteParcel_consignment');

        $query = "SELECT * FROM {$table} WHERE order_id = '{$id_order}'";
        return $connection->fetchAll($query);
    }

    public function isCashToCollect($id_order)
    {
        $allowedChargeCodes = array('CS1', 'CS2', 'CS3', 'CS4', 'CS5', 'CS6', 'CS7', 'CS8', 'CX1', 'CX2');

        $chargeCode = $this->getOrderChargeCode($id_order);
        if (in_array($chargeCode, $allowedChargeCodes)) {
            return true;
        }
        return false;
    }

    public function getArticles($id_order, $consignment_number)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $table = $resource->getTableName('biztech_ausposteParcel_article');

        $query = "SELECT * FROM {$table} WHERE order_id = '{$id_order}' AND consignment_number='{$consignment_number}'";

        return $connection->fetchAll($query);
    }

    public function getArticle($id_order, $consignment_number, $articleNumber)
    {
        $articles = $this->articleCollectionFactory->create()->addFieldToFilter('consignment_number', $consignment_number)
            ->addFieldToFilter('order_id', $id_order)
            ->addFieldToFilter('consignment_number', $consignment_number)
            ->addFieldToFilter('article_number', $articleNumber);

        foreach ($articles as $article) {
            return $article;
        }
    }

    public function isAddressValid($id)
    {
        $order = $this->order->load($id);
        return true;
    }

    public function getNonauspostShippingTypes()
    {
        $collection = $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Order\Collection');
        $collection->addAttributeToFilter('shipping_method', array('nlike' => '%ausposteParcel%'));
        $collection
            ->getSelect()
            ->order('main_table.shipping_description asc')
            ->group('main_table.shipping_description');

        $options = array();
        foreach ($collection as $order) {
            $options[$order->getShippingDescription()] = $order->getShippingDescription();
        }
        return $options;
    }

    public function getNonauspostShippingTypeOptions()
    {
        $getNonauspostShippingTypes = $this->getNonauspostShippingTypes();
        $options = array();
        $option = array('value' => '', 'label' => 'Please Select');
        $options[] = $option;

        foreach ($getNonauspostShippingTypes as $key => $val) {
            $option = array('value' => $key, 'label' => $val);
            $options[] = $option;
        }
        return $options;
    }

    public function getOrderWeight($order)
    {
        $weight = $order->getWeight();
        $product_unit = trim($this->scopeConfig->getValue('carriers/ausposteParcel/productWeightUnit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $packaging_allowance_type = trim($this->scopeConfig->getValue('carriers/ausposteParcel/packagingAllowanceType', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if ($weight > 0 && $product_unit == 'grams') {
            $weight = $weight / 1000;
            $weight = number_format($weight, 2, '.', '');
        }

        $packaging_allowance_value = trim($this->scopeConfig->getValue('carriers/ausposteParcel/packagingAllowanceValue', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if ($packaging_allowance_value > 0) {
            if ($packaging_allowance_type == 'F') {
                $weight += $packaging_allowance_value;
            } else {
                $weight += ($weight * ($packaging_allowance_value / 100));
            }
        }
        return $weight;
    }

    public function articletypeMatch($articletypes, $weight)
    {
        $selected = false;
        if ($articletypes && count($articletypes) > 0) {
            foreach ($articletypes as $articletype) {
                $articletypeWeight = floatval($articletype->getWeight());
                $weight = floatval($weight);

                $articletypeWeight = '' . $articletypeWeight . '';
                $weight = '' . $weight . '';

                if ($articletypeWeight == $weight) {
                    $selected = true;
                    break;
                }
            }
        }
        return $selected;
    }

    public function isOrderAddressValid($order_id, $force = false)
    {
        $order = $this->order->load($order_id);
        if (!$force && $order->getIsAddressValid()) {
            return 1;
        }
        $address = $order->getShippingAddress();
        $status = 1; //$this->isAddressValid($address);
        if ($status == 1) {
            $order->setIsAddressValid(1);
        } else {
            $order->setIsAddressValid(0);
        }
        $order->save();
        return true;
    }

    public function getTemplatePath()
    {
        $directory = $this->_objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

        $codePath = $directory->getRoot();
        $etcPath = $codePath . DIRECTORY_SEPARATOR . 'app/code/Biztech/Ausposteparcel/etc';
        if (file_exists($etcPath)) {
          return $etcPath;
      } else {
          return $codePath . DIRECTORY_SEPARATOR . 'vendor/biztech/ausposteparcel/etc';
      }
    }

    public function getManifest($manifest_number)
    {
        $manifest = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest')->getCollection()->addFieldToFilter('manifest_number', $manifest_number)->getData();

        if (count($manifest) > 0) {
            return $manifest[0]['manifest_id'];
        }
        return false;
    }

    public function getManifestData($manifest_number)
    {
        $manifest = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest')->getCollection()->addFieldToFilter('manifest_number', $manifest_number)->getData();

        if (count($manifest) > 0) {
            return $manifest[0];
        }
        return false;
    }

    public function getChargeCode($order, $consignmentNumber = '')
    {
        $chargeCode = $this->getOrderChargeCode($order->getId(), $consignmentNumber);
        return $chargeCode;
    }

    public function getIncrementId($order)
    {
        $incrementId = $order->getOriginalIncrementId();
        if ($incrementId == null || empty($incrementId) || !$incrementId) {
            $incrementId = $order->getIncrementId();
        }
        return $incrementId;
    }

    public function isCurrentMainfestHasConsignmentsForDespatch()
    {
        $consignment = $this->consignmentCollectionFactory->create()->addFieldToFilter('despatched', 0)
            ->addFieldToFilter('is_next_manifest', 1);

        if (count($consignment) > 0) {
            return true;
        }
        return false;
    }

    public function getDeliveryTypeOptions()
    {
        $options = array();
        $title = $this->scopeConfig->getValue('carriers/ausposteParcel/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $collection = $this->_objectManager->create("Biztech\Ausposteparcel\Model\Auspostlabel")->getCollection();
        //$collection = $this->auspostlabelCollectionFactory->create();
        $type = array();
        foreach ($collection->getData() as $key => $value) {
            $type["ausposteParcel_ausposteParcel-" . $value['charge_code']] = ucfirst($value['type']);
        }
        if ($this->scopeConfig->getValue('carriers/ausposteParcel/eParcelShippingApplyAll', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $chargecode = $this->scopeConfig->getValue('carriers/ausposteParcel/defaultChargeCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (!in_array($chargecode, $type)) {
                $type[$chargecode] = $chargecode;
            }
        }
        return $type;
    }

    public function getConsignmentLabelUrl()
    {
        $this->_storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $storeUrl .= 'biztech/ausposteParcel/label/consignment/';
        return $storeUrl;
    }

    public function getConsignmentReturnLabelUrl()
    {
        $this->_storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $storeUrl .= 'biztech/ausposteParcel/label/returnlabels/';
        return $storeUrl;
    }

    public function isReturnLabelFileExists($consignmentNumber)
    {
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        $filepath = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . '/' . 'ausposteParcel' . '/' . 'label' . '/' . 'returnlabels' . '/' . $filename;
        return file_exists($filepath);
    }

    public function getManifestLabelUrl()
    {
        $this->_storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $storeUrl .= 'biztech/ausposteParcel/label/manifest/';
        return $storeUrl;
    }

    public function getManifestNumber()
    {
        try {
            $manifestNumber = false;
            $manifests = $this->ausposteParcelApiFactory->create()->getManifest();
            $xml = simplexml_load_string($manifests);
            $this->logger->log(null, 'manifest xml: ' . preg_replace('/\s+/', ' ', trim($manifests)));
            $currentManifest = '';
            if ($xml) {
                foreach ($xml->manifest as $manifest) {
                    $manifestNumber = $manifest->manifestNumber;
                    if (empty($currentManifest)) {
                        $currentManifest = $manifestNumber;
                    }

                    $numberOfArticles = (int) $manifest->numberOfArticles;
                    $numberOfConsignments = (int) $manifest->numberOfConsignments;
                    $this->ausposteParcelHelper->updateManifest($manifestNumber, $numberOfArticles, $numberOfConsignments);
                }

                $config_model = $this->_objectManager->get('\Magento\Framework\App\Config\Storage\WriterInterface');
                $config_model->save('carriers/ausposteParcel/manifestSync', 0, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
            }
            return $currentManifest;
        } catch (\Exception $e) {
            $this->logger->log(null, 'getManifestNumber: ' . $e->getMessage());
            return false;
        }
    }

    public function xmlData($text)
    {
        $text = trim($text);
        $text = str_replace('&', '&amp;', $text);
        $search = array("<", ">", '"', "'");
        $replace = array("&lt;", "&gt;", "&quot;", "&apos;");
        return str_replace($search, $replace, $text);
    }

    public function getEparcelShippingOptions($currentMethod, $changeDescription)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('biztech_ausposteParcel_nonausposteParcel');
        $query = "SELECT * FROM {$tableName}";
        $types = $connection->fetchAll($query);
        $options = array();
        foreach ($types as $code => $val) {
            $auspostlabelChargeCodes = $this->ausposteParcelAuspostlabelFactory->create()->load($val['charge_code'], 'charge_code');
            $auspostlabels = $auspostlabelChargeCodes->getData();
            if (empty($auspostlabels)) { continue;
            }
            $s1 = 'ausposteParcel_ausposteParcel-' . $val['charge_code'];
            $s2 = $auspostlabels['type'];
            $code1 = base64_encode($s1 . '###' . $s2);
            $order_method = base64_encode($currentMethod . '###' . $changeDescription);
            if ($code1 != $order_method) {
                $options[$code1] = $s2;
            }
        }
        return $options;
    }

    public function getOrderShippingTypes()
    {
        $collection = $this->order;
        $collection->addAttributeToFilter('shipping_method', array('like' => '%ausposteParcel%'));
        $collection
            ->getSelect()
            ->order('main_table.shipping_description asc')
            ->group('main_table.shipping_description');

        $options = array();
        foreach ($collection as $order) {
            $method = $order->getShippingMethod();
            $description = $order->getShippingDescription();
            $options[$method . '###' . $description] = $order->getShippingDescription();
        }
        return $options;
    }

    public function getShippingAddress($order_id)
    {
        $resource = $this->_resource;
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('sales_order_address');
        $table2 = $resource->getTableName('sales_order');

        $query = "SELECT order_address.* FROM {$table} as order_address LEFT JOIN {$table2} as order_table ON order_table.shipping_address_id = order_address.entity_id WHERE order_table.entity_id = '{$order_id}'";

        $addresses = $readConnection->fetchAll($query);
        foreach ($addresses as $address) {
            return $address;
        }
        return false;
    }

    public function getExpressPostCodes()
    {
        $codes1 = array('X1', 'X2', 'X5', 'X6', 'XB1', 'XB2', 'XB3', 'XB4', 'XB5', 'XDE5', 'XW5', 'XW6', 'XS', '7J55');
        $codes2 = array('2G33', '2G35', '2H33', '2H35', '2I33', '2I35', '2J33', '2J35', '3H03', '3H05', '3I03', '3I05', '3I33', '3I35', '3I53', '3I55', '3I83', '3I85', '3J03', '3J05', '3J33', '3J35', '3J53', '3J55', '3J83', '3J85', '3K03', '3K05', '3K33', '3K35', '3K53', '3K55', '3K83', '3K85', '4I33', '4I35', '4J33', '4J35', '7H03', '7H05', '7H33', '7H35', '7H53', '7H55', '7H83', '7H85', '7I03', '7I05', '7I33', '7I35', '7I53', '7I55', '7I83', '7I85', '7J03', '7J05', '7J33', '7J35', '7J53', '7J55', '7J83', '7J85', '7K03', '7K05', '7K33', '7K35', '7K53', '7K55', '7K83', '7K85', '7T33', '7T35', '7T83', '7T85', '7U33', '7U35', '7U83', '7U85', '7V33', '7V35', '7V83', '7V85', '8G33', '8G35', '8H33', '8H35', '8I33', '8I35', '8J33', '8J35', '9G33', '9G35', '9H33', '9H35', '9I33', '9I35', '9J33', '9J35');
        return array_merge($codes1, $codes2);
    }

    public function isExpressPostCode($code)
    {
        $codes = $this->getExpressPostCodes();
        return in_array($code, $codes);
    }

    public function getAusposteParcelStandardCodes()
    {
        $codes1 = array('B1', 'B2', 'B3', 'B4', 'B5', 'B96', 'B97', 'B98', 'D1', 'DE1', 'DE2', 'DE4', 'DE5', 'DE6', 'MED1', 'MED2', 'S1', 'S10', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'SV1', 'SV2', 'W5', 'W6', '7D55');
        $codes2 = array('3E03', '7E03', '3E05', '7E05', '3E33', '7E33', '3E35', '7E35', '3E53', '7E53', '3E55', '7E55', '3E83', '7E83', '3E85', '7E85', '2A33', '2A35', '2B33', '2B35', '2C33', '2C35', '2D33', '2D35', '3B03', '3B05', '3C03', '3C05', '3C33', '3C35', '3C53', '3C55', '3C83', '3C85', '3D03', '3D05', '3D33', '3D35', '3D53', '3D55', '3D83', '3D85', '4A33', '4A35', '4B33', '4B35', '4C33', '4C35', '4D33', '4D35', '7B03', '7B05', '7B33', '7B35', '7B53', '7B55', '7B83', '7B85', '7C03', '7C05', '7C33', '7C35', '7C53', '7C55', '7C83', '7C85', '7D03', '7D05', '7D33', '7D35', '7D53', '7D55', '7D83', '7D85', '7N33', '7N35', '7N83', '7N85', '7O33', '7O35', '7O83', '7O85', '7P33', '7P35', '7P83', '7P85', '8A33', '8A35', '8B33', '8B35', '8C33', '8C35', '8D33', '8D35', '9A33', '9A35', '9B33', '9B35', '9C33', '9C35', '9D33', '9D35');
        return array_merge($codes1, $codes2);
    }

    public function isAusposteParcelStandardCode($code)
    {
        $codes = $this->getAusposteParcelStandardCodes();
        return in_array($code, $codes);
    }
}
