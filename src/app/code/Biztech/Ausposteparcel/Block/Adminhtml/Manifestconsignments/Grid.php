<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Manifestconsignments;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Biztech\AusposteParcel\Model\Mysql4\Consignment\CollectionFactory
     */
    protected $consignmentCollectionFactory;

    /**
     * @var \Biztech\AusposteParcel\Helper\Info
     */
    protected $ausposteParcelInfoHelper;
    protected $order;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Biztech\Ausposteparcel\Model\Cresource\Consignment\Collection $consignmentCollectionFactory,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\Order $order,
        array $data = []
    ) {
        $this->consignmentCollectionFactory = $consignmentCollectionFactory;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->_resource = $resource;
        $this->order = $order;
        parent::__construct($context, $backendHelper, $data);

        $this->setId('manifestconsignmentsGrid');
        $this->setDefaultSort('consignment_number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getSearchButtonHtml()
    {
        return parent::getSearchButtonHtml();
    }

    protected function _prepareCollection()
    {
        $manifest_number = $this->getRequest()->getParam('manifest');
        $connection = $this->_resource->getConnection();
        // $collection = $this->consignmentCollectionFactory->load()->addFieldToFilter('main_table.manifest_number', array('eq' => $manifest_number));
        $collection = $this->order->getCollection();
        $collection
                ->getSelect()
                ->join(array('consignment' => $this->_resource->getTableName('biztech_ausposteParcel_consignment')), 'main_table.entity_id = consignment.order_id')
                ->join(array('order_address' => $this->_resource->getTableName('sales_order_address')), 'main_table.shipping_address_id = order_address.entity_id')
                ->joinLeft(array('manifest' => $this->_resource->getTableName('biztech_ausposteParcel_manifest')), 'consignment.manifest_number = manifest.manifest_number', array('despatch_date'))
                ->where("manifest.manifest_number = '$manifest_number'")
                ->joinLeft(array('article' => $this->_resource->getTableName('biztech_ausposteParcel_article')), 'consignment.consignment_number = article.consignment_number', array('number_of_articles' => 'COUNT(article_number)'))
                ->columns(new \Zend_Db_Expr("CONCAT_WS(' ',`order_address`.`firstname`,`order_address`.`lastname`) AS fullname"))
                ->columns('consignment.weight as total_consignment_weight')
                ->group('consignment.consignment_number');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => __('Order #'),
            'align' => 'left',
            'index' => 'increment_id',
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment\Number'
        ));

        $this->addColumn('customer_name', array(
            'header' => __('Ship to Name'),
            'align' => 'left',
            'index' => 'fullname',
            'sortable' => true,
            'filter_condition_callback' => array($this, 'customerNameCondition')
        ));

        $this->addColumn('state', array(
            'header' => __('State/Province'),
            'index' => 'region',
            'sortable' => true
        ));

        $this->addColumn('postcode', array(
            'header' => __('ZIP'),
            'index' => 'postcode',
            'sortable' => true
        ));

        $this->addColumn('total_consignment_weight', array(
            'header' => __('Weight (Kgs)'),
            'index' => 'total_consignment_weight',
            'filter' => false,
            'sortable' => true
        ));

        $this->addColumn('consignment_number', array(
            'header' => __('Consignment #'),
            'align' => 'center',
            'index' => 'consignment_number',
            'filter_index' => 'consignment.consignment_number',
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment\Number'
        ));

        $this->addColumn('add_date', array(
            'header' => __('Created On'),
            'align' => 'center',
            'type' => 'datetime',
            'index' => 'add_date',
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment\Date'
        ));

        $this->addColumn('despatch_date', array(
            'header' => __('Dispatched On'),
            'align' => 'center',
            'type' => 'datetime',
            'index' => 'despatch_date',
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment\Date'
        ));

        $this->addColumn('number_of_articles', array(
            'header' => __('No. of Articles'),
            'align' => 'center',
            'index' => 'number_of_articles',
            'sortable' => true,
            'filter_condition_callback' => array($this, 'totalArticlesCondition')
        ));

        $this->addColumn('label', array(
            'header' => __('Label'),
            'align' => 'center',
            'index' => 'label',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment\Labelprint'
        ));

        $this->addColumn('track_url', array(
            'header' => __('Track'),
            'align' => 'center',
            'index' => 'consignment_number',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment\Track'
        ));

        $this->addColumn('is_submitted_to_eparcel', array(
            'header' => __('Is Submitted to eParcel?'),
            'align' => 'center',
            'index' => 'is_submitted_to_eparcel',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Consignment\IsSubmittedEparcel'
        ));

        return parent::_prepareColumns();
    }

    protected function customerNameCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $consignmentNumbers = array();
        foreach ($this->consignmentCollectionFactory->create() as $key => $item) {
            $consignment_number = $item->getConsignmentNumber();
            $orderId = $item->getOrderId();
            $address = $this->ausposteParcelInfoHelper->getShippingAddress($orderId);
            $firstname = $address['firstname'];
            $lastname = $address['lastname'];
            $fullname = $firstname . ' ' . $lastname;

            if (preg_match('/' . $value . '/i', $fullname)) {
                $consignmentNumbers[] = $consignment_number;
            }
        }

        if (count($consignmentNumbers) == 0) {
            $this->getCollection()->addFieldToFilter('consignment.consignment_number', array('nin' => $consignmentNumbers));
        } else {
            $this->getCollection()->addFieldToFilter('consignment.consignment_number', array('in' => $consignmentNumbers));
        }
    }

    protected function totalArticlesCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        if (!is_numeric($value)) {
            return;
        }

        $consignmentNumbers = array();
        foreach ($this->consignmentCollectionFactory->create() as $key => $item) {
            $consignment_number = $item->getConsignmentNumber();
            $orderId = $item->getOrderId();
            $articles = $this->ausposteParcelInfoHelper->getArticles($orderId, $consignment_number);
            $totalArticles = count($articles);

            if ($totalArticles == $value) {
                $consignmentNumbers[] = $consignment_number;
            }
        }

        if (count($consignmentNumbers) == 0) {
            $this->getCollection()->addFieldToFilter('consignment.consignment_number', array('nin' => $consignmentNumbers));
        } else {
            $this->getCollection()->addFieldToFilter('consignment.consignment_number', array('in' => $consignmentNumbers));
        }
    }
}