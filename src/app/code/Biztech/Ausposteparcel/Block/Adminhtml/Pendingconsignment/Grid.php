<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Pendingconsignment;
use Magento\Framework\DB\Select;
/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    public $order;

    /**
     * @var \Biztech\AusposteParcel\Helper\Info
     */
    protected $ausposteParcelInfoHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->order = $order;
        $this->_resource = $resource;
        parent::__construct($context, $backendHelper, $data);

        $this->setId('pending_consignment');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
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
        $status_condition = 'main_table.status = "processing" OR main_table.status = "pending" OR';
        $display_choosen_status = (int) $this->scopeConfig->getValue('carriers/ausposteParcel/displayChoosenStatus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($display_choosen_status == 1) {
            $chosen_statuses = $this->scopeConfig->getValue('carriers/ausposteParcel/chooseStatuses', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (!empty($chosen_statuses)) {
                $chosen_statuses = explode(',', $chosen_statuses);
                if (count($chosen_statuses) > 0) {
                    $status_condition = '';
                    foreach ($chosen_statuses as $chosen_status) {
                        if (!empty($chosen_status)) {
                            $status_condition .= 'main_table.status="' . $chosen_status . '" OR ';
                        }
                    }
                }
            }
        }
        $connection = $this->_resource->getConnection();
        if ($this->scopeConfig->getValue('carriers/ausposteParcel/eParcelShippingApplyAll', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('carriers/ausposteParcel/defaultChargeCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != '') {
            $collection = $this->order->getCollection();
            $collection->getSelect()->joinLeft(array('order_address' => $this->_resource->getTableName('sales_order_address')),
                'main_table.shipping_address_id=order_address.entity_id')->reset(Select::COLUMNS)->columns(new \Zend_Db_Expr("CONCAT(`order_address`.`firstname`, ' ',`order_address`.`lastname`) AS fullname"))
                    ->columns('CONCAT(main_table.entity_id, "_",IFNULL(c.consignment_number,0)) as order_consignment')
                    ->columns('main_table.customer_firstname')
                    ->columns('main_table.customer_lastname')
                    ->columns('main_table.is_address_valid')
                    ->columns('main_table.increment_id')
                    ->columns('main_table.shipping_method')
                    ->columns('main_table.status')
                    ->columns('main_table.shipping_description')
                    ->columns('c.general_ausposteParcel_shipping_chargecode as general_ausposteParcel_shipping_chargecode')
                    ->columns("(case when c.consignment_number != '' then ( select count(*) from " . $this->_resource->getTableName('biztech_ausposteParcel_article') . " where consignment_number = c.consignment_number) else null end) as number_of_articles")
                    ->columns('is_address_valid as is_not_open')
                    ->joinLeft(array('c' => $this->_resource->getTableName('biztech_ausposteParcel_consignment')), 'main_table.entity_id = c.order_id and c.despatched=0')
                    ->where(' (main_table.shipping_method like "%ausposteParcel%" OR 
                                                    (
                                                    case 
                                                    when (select count(*) from ' . $this->_resource->getTableName('biztech_ausposteParcel_nonausposteParcel') . ' where method = main_table.shipping_description and  charge_code != "none") > 0 
                                                    then 1 
                                                    when (select charge_code from ' . $this->_resource->getTableName('biztech_ausposteParcel_nonausposteParcel') . ' where method = main_table.shipping_description) = "none"
                                                    then 0 
                                                    else 1
                                                    end
                                                    ) > 0
                                    )')
                    ->where(' (' . $status_condition . '
                                                    (case when (select count(*) from ' . $this->_resource->getTableName('biztech_ausposteParcel_consignment') . ' where order_id = main_table.entity_id) > 0 then (select count(*) from ' . $this->_resource->getTableName('biztech_ausposteParcel_consignment') . ' where order_id = main_table.entity_id and despatched = 0) else 0 end) > 0
                                    )')
                    ->where("order_address.country_id='AU'")
                    ->where("c.manifest_number IS NULL and c.consignment_number IS NOT NULL");
            $this->setCollection($collection);
            return parent::_prepareCollection();
        } else {
            $collection = $this->order->getCollection();
            $collection
                    ->getSelect()
                    ->joinLeft(array('order_address' => $this->_resource->getTableName('sales_order_address')), 'main_table.shipping_address_id = order_address.entity_id')
                    ->reset(Select::COLUMNS)
                    ->columns(new \Zend_Db_Expr("CONCAT(`order_address`.`firstname`, ' ',`order_address`.`lastname`) AS fullname"))
                    ->columns('CONCAT(main_table.entity_id, "_",IFNULL(c.consignment_number,0)) as order_consignment')
                    ->columns('main_table.customer_firstname')
                    ->columns('main_table.customer_lastname')
                    ->columns('main_table.is_address_valid')
                    ->columns('main_table.increment_id')
                    ->columns('main_table.shipping_method')
                    ->columns('main_table.status')
                    ->columns('main_table.shipping_description')
                    ->columns('c.general_ausposteParcel_shipping_chargecode as general_ausposteParcel_shipping_chargecode')
                    ->columns("(case when c.consignment_number != '' then ( select count(*) from " . $this->_resource->getTableName('biztech_ausposteParcel_article') . " where consignment_number = c.consignment_number) else null end) as number_of_articles")
                    ->columns('is_address_valid as is_not_open')
                    ->joinLeft(array('c' => $this->_resource->getTableName('biztech_ausposteParcel_consignment')), 'main_table.entity_id = c.order_id and c.despatched=0')
                    ->where(' (main_table.shipping_method like "%ausposteParcel%" OR 
                                                        (
                                                        case 
                                                        when (select count(*) from ' . $this->_resource->getTableName('biztech_ausposteParcel_nonausposteParcel') . ' where method = main_table.shipping_description and  charge_code != "none") > 0 
                                                        then 1 
                                                        else 0
                                                        end
                                                        ) > 0
                                        )')
                    ->where(' (' . $status_condition . ' 
                                                        (case when (select count(*) from ' . $this->_resource->getTableName('biztech_ausposteParcel_consignment') . ' where order_id = main_table.entity_id) > 0 then (select count(*) from ' . $this->_resource->getTableName('biztech_ausposteParcel_consignment') . ' where order_id = main_table.entity_id and despatched = 0) else 0 end) > 0
                                        )')
                    ->where("c.manifest_number IS NULL and c.consignment_number IS NOT NULL");
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => __('Order #'),
            'align' => 'center',
            'index' => 'increment_id',
            'filter' => false,
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Ordernumber'
        ));

        $this->addColumn('customer_name', array(
            'header' => __('Ship to Name'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'fullname',
            'filter' => false,
            'sortable' => true
        ));

        $display_order_status = (int) $this->scopeConfig->getValue('carriers/ausposteParcel/displayOrderStatus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($display_order_status == 1) {
            $this->addColumn('status', array(
                'header' => __('Status'),
                'align' => 'center',
                'index' => 'status',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Status'
            ));
        }

        $this->addColumn('weight', array(
            'header' => __('Weight (Kgs)'),
            'align' => 'center',
            'index' => 'weight',
            'filter' => false,
            'sortable' => true
        ));

        $this->addColumn('is_address_valid', array(
            'header' => __('Is Valid Address ?'),
            'align' => 'center',
            'index' => 'is_address_valid',
            'type' => 'options',
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            ),
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Isvalidaddress'
        ));

        $this->addColumn('consignment_number', array(
            'header' => __('Consignment #'),
            'align' => 'center',
            'index' => 'c.consignment_number',
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Number'
        ));

        $this->addColumn('shipping_method', array(
            'header' => __('Shipping Method'),
            'align' => 'left',
            'index' => 'shipping_method',
            'type' => 'options',
            'options' => $this->ausposteParcelInfoHelper->getDeliveryTypeOptions(),
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Shippingmethod',
            'sortable' => true,
            'filter' => false,
            'filter_condition_callback' => array($this, 'shippingMethodCondition')
        ));

        $this->addColumn('is_label_created', array(
            'header' => __('Is Labels Created ?'),
            'align' => 'center',
            'index' => 'c.is_label_created',
            'type' => 'options',
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            ),
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Islabelcreated'
        ));

        $this->addColumn('is_label_printed', array(
            'header' => __('Is Labels Printed ?'),
            'align' => 'center',
            'index' => 'c.is_label_printed',
            'type' => 'options',
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            ),
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Islabelprinted'
        ));

        $this->addColumn('is_return_label_printed', array(
            'header' => __('Is Return Labels Printed'),
            'align' => 'center',
            'index' => 'c.is_return_label_printed',
            'type' => 'options',
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            ),
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Returnlabelprinted'
        ));

        $this->addColumn('is_next_manifest', array(
            'header' => __('Is Under Current Manifest ?'),
            'align' => 'center',
            'index' => 'c.is_next_manifest',
            'type' => 'options',
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            ),
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Iscurrentmanifest'
        ));

        $this->addColumn('number_of_articles', array(
            'header' => __('No. of Articles'),
            'align' => 'center',
            'index' => 'number_of_articles',
            'sortable' => true,
            'filter' => false,
        ));

        $this->addColumn('add_date', array(
            'header' => __('Created On'),
            'align' => 'center',
            'index' => 'add_date',
            'type' => 'datetime',
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Date'
        ));

        $this->addColumn('modify_date', array(
            'header' => __('Modified On'),
            'align' => 'center',
            'index' => 'modify_date',
            'type' => 'datetime',
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Date'
        ));

        return parent::_prepareColumns();
    }

    protected function shippingMethodCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->getSelect()->where("(main_table.shipping_method like '%_$value' or c.general_ausposteParcel_shipping_chargecode = '$value')");
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('order_consignment');
        $this->getMassactionBlock()->setFormFieldName('order_consignment');
        $this->setFilterVisibility(false);
        
        $this->getMassactionBlock()->setUseSelectAll(false);

        $active = (int) $this->scopeConfig->getValue('carriers/ausposteParcel/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($active == 1) {
            $this->getMassactionBlock()->addItem('assign', array(
                'label' => __('Add to Current Manifest'),
                'url' => $this->getUrl('*/*/massAssignPendingConsignment', array('manifest_number' => $this->getRequest()->getParam('manifest_number')))
            ));
        }
        return $this;
    }
}
