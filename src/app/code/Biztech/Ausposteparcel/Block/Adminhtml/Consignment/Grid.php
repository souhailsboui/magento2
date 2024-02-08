<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Consignment;

use Biztech\Ausposteparcel\Model\Auspostlabel;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    protected $_collectionFactory;
    protected $messageManager;
    protected $ausposteparcelHelper;
    protected $order;
    protected $auspostlabel;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Biztech\Ausposteparcel\Model\Cresource\Consignment\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Biztech\Ausposteparcel\Helper\Data $eparcelHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Message\ManagerInterface $messageinterface,
        \Biztech\Ausposteparcel\Helper\Info $info,
        Auspostlabel $auspostlabel,
        array $data = []
    ) {
        $this->order = $order;
        $this->ausposteparcelHelper = $eparcelHelper;
        $this->messageManager = $messageinterface;
        $this->_collectionFactory = $collectionFactory;
        $this->moduleManager = $moduleManager;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_resource = $resource;
        $this->info = $info;
        $this->auspostlabel = $auspostlabel;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('biztech_ausposteparcel/consignment/index', ['_current' => true]);
    }

    /*public function getRowUrl($row)
    {
        return false;
    }*/
    
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('consignmentGrid');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        try {
            if (!empty($this->ausposteparcelHelper->getAllWebsites())) {
            
                $status_condition = 'main_table.status = "processing" OR main_table.status = "pending" OR main_table.status = "complete" OR ';
                $display_choosen_status = $this->scopeConfig->getValue('carriers/ausposteParcel/displayChoosenStatus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
                if ($this->scopeConfig->getValue('carriers/ausposteParcel/eParcelShippingApplyAll', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $status_condition1 = strrpos($status_condition, "OR");
                    if ($status_condition1 !== false) {
                        $status_condition = substr_replace($status_condition, " ", $status_condition1, strlen($status_condition));
                    }

                    $collection = $this->order->getCollection()->addAttributeToFilter('store_id', ['in' => [$this->ausposteparcelHelper->getAllWebsites()]]);
                    $collection
                    ->getSelect()
                    ->joinLeft(['order_address' => $this->_resource->getTableName('sales_order_address')], 'main_table.shipping_address_id = order_address.entity_id')
                    ->reset(\Magento\Framework\DB\Select::COLUMNS)
                    ->columns(new \Zend_Db_Expr("CONCAT_WS(' ',`order_address`.`firstname`,`order_address`.`lastname`) AS fullname"))
                    ->columns('CONCAT(main_table.entity_id, "_",IFNULL(c.consignment_number,0)) as order_consignment')
                    ->columns('main_table.customer_firstname')
                    ->columns('main_table.customer_lastname')
                    ->columns('main_table.is_address_valid')
                    ->columns('main_table.eparcel_shipping_id as eparcelShippingId')
                    ->columns('main_table.increment_id')
                    ->columns('main_table.weight as order_weight')
                    ->columns('main_table.shipping_method')
                    ->columns('main_table.status')
                    ->columns('main_table.created_at')
                    ->columns('main_table.is_label_printed as labelprinted')
                    ->columns('main_table.shipping_description')
                    ->columns('c.general_ausposteParcel_shipping_chargecode as general_ausposteParcel_shipping_chargecode')
                    ->columns('c.is_submitted_to_eparcel as is_submitted_to_eparcel')
                    ->columns("(case when c.consignment_number != '' then ( select count(*) from " . $this->_resource->getTableName('biztech_ausposteParcel_article') . " where consignment_number = c.consignment_number) else null end) as number_of_articles")
                    ->columns('is_address_valid as is_not_open')
                    ->joinLeft(['c' => $this->_resource->getTableName('biztech_ausposteParcel_consignment')], 'main_table.entity_id = c.order_id')
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
                        ->where($status_condition)
                        ->where("main_table.shipping_method !=''")
                        ->where("order_address.country_id='AU'");
                    $collection->setOrder('created_at', 'DESC');
                    $this->setCollection($collection);
                    return parent::_prepareCollection();
                } else {
                    $status_condition1 = strrpos($status_condition, "OR");
                    if ($status_condition1 !== false) {
                        $status_condition = substr_replace($status_condition, " ", $status_condition1, strlen($status_condition));
                    }
                    $collection = $this->order->getCollection()->addAttributeToFilter('store_id', ['in' => [$this->ausposteparcelHelper->getAllWebsites()]]);
                    $collection
                    ->getSelect()
                    ->joinLeft(['order_address' => $this->_resource->getTableName('sales_order_address')], 'main_table.shipping_address_id = order_address.entity_id')
                    ->reset(\Magento\Framework\DB\Select::COLUMNS)
                    ->columns(new \Zend_Db_Expr("CONCAT_WS(' ',`order_address`.`firstname`,`order_address`.`lastname`) AS fullname"))
                    ->columns('CONCAT(main_table.entity_id, "_",IFNULL(c.consignment_number,0)) as order_consignment')
                    ->columns('main_table.customer_firstname')
                    ->columns('main_table.customer_lastname')
                    ->columns('main_table.is_address_valid')
                    ->columns('main_table.is_label_printed as labelprinted')
                    ->columns('main_table.increment_id')
                    ->columns('main_table.eparcel_shipping_id as eparcelShippingId')
                    ->columns('main_table.shipping_method')
                    ->columns('main_table.status')
                    ->columns('main_table.created_at')
                    ->columns('main_table.weight as order_weight')
                    ->columns('main_table.shipping_description')
                    ->columns('main_table.is_label_generated')
                    ->columns('c.general_ausposteParcel_shipping_chargecode as general_ausposteParcel_shipping_chargecode')
                    ->columns('c.is_submitted_to_eparcel as is_submitted_to_eparcel')
                    ->columns("(case when c.consignment_number != '' then ( select count(*) from " . $this->_resource->getTableName('biztech_ausposteParcel_article') . " where consignment_number = c.consignment_number) else null end) as number_of_articles")
                    ->columns('is_address_valid as is_not_open')
                    ->joinLeft(['c' => $this->_resource->getTableName('biztech_ausposteParcel_consignment')], 'main_table.entity_id = c.order_id')
                    ->where(' (main_table.shipping_method like "%ausposteParcel%" OR (
                        case 
                        when (select count(*) from ' . $this->_resource->getTableName('biztech_ausposteParcel_nonausposteParcel') . ' where method = main_table.shipping_description and  charge_code != "none") > 0 
                        then 1 
                        else 0
                        end
                        ) > 0
                        )')
                    ->where($status_condition)
                    ->where("main_table.shipping_method !=''");

                    $collection->setOrder('created_at', 'DESC');
                    $this->setCollection($collection);
                    return parent::_prepareCollection();
                }
            } else {
                $this->messageManager->addError(__('Extension- Australia Post Parcel Send is not enabled. Please enable it from Store > Configuration > Sales > Shipping Methods -> Appjetty Australia Post Parcel Send.'));
                return $this;
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return $this;
        }
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    /*  protected function _addColumnFilterToCollection($column)
      {
      if ($this->getCollection()) {
      if ($column->getId() == 'websites') {
      $this->getCollection()->joinField(
      'websites', 'catalog_product_website', 'website_id', 'product_id=entity_id', null, 'left'
      );
      }
      }
      return parent::_addColumnFilterToCollection($column);
      } */

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order #'),
                'align' => 'left',
                'width' => '200px',
                'class' => 'name',
                'type' => 'text',
                'index' => 'increment_id',
                'sortable' => false,
                'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Order'
            ]
        );

        $this->addColumn('created_at', [
            'header' => __('Purchase Date'),
            'align' => 'center',
            'index' => 'created_at',
            'type' => 'datetime',
            'sortable' => false
        ]);

        $this->addColumn('customer_name', [
            'header' => __('Ship to Name'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'fullname',
            'filter' => false,
            'sortable' => false
        ]);

        $display_order_status = $this->scopeConfig->getValue('carriers/ausposteParcel/displayOrderStatus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($display_order_status == 1) {
            $this->addColumn('status', [
                'header' => __('Status'),
                'align' => 'center',
                'index' => 'status',
                'type' => 'options',
                'options' => $this->getOptions(),
                // 'filter' => false,
                'sortable' => false,
                'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Status'
            ]);
        }

        $this->addColumn(
            'weight',
            [
                'header' => __('Consignment Weight (Kgs)'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'weight',
                'class' => 'name',
                'filter' => false,
                'sortable' => false
            ]
        );
        $this->addColumn(
            'order_weight',
            [
                'header' => __('Order Weight (Kgs)'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'order_weight',
                'class' => 'name',
                'filter' => false,
                'sortable' => false
            ]
        );
        $this->addColumn(
            'is_valid_address',
            [
                'header' => __('Is Valid Address ?'),
                'align' => 'left',
                'width' => '200px',
                'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Isvalidaddress',
                'class' => 'name',
                'type' => 'options',
                'filter' => false,
                'options' => [
                    1 => 'Yes',
                    0 => 'No',
                ],
                'sortable' => false
            ]
        );
        $this->addColumn(
            'consignment_number',
            [
                'header' => __('Consignment #'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'consignment_number',
                'class' => 'name',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Number'
            ]
        );
        $this->addColumn(
            'shipping_method',
            [
                'header' => __('Shipping Method'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'shipping_method',
                'class' => 'name',
                'sortable' => false,
                'type' => 'options',
                // 'filter' => false,
                'options' => $this->info->getDeliveryTypeOptions(),
                'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Shippingmethod',
            ]
        );

        $this->addColumn('is_label_generated', [
            'header' => __('Is Lable Created'),
            'align' => 'center',
            'index' => 'is_label_generated',
            'type' => 'options',
            'options' => [
                1 => 'Yes',
                0 => 'No',
            ],
            // 'filter' => false,
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Islabelcreated',
        ]);

        $this->addColumn('labelprinted', [
            'header' => __('Is Label Printed'),
            'align' => 'center',
            'index' => 'main_table.is_label_printed',
            'type' => 'options',
            'options' => [
                1 => 'Yes',
                0 => 'No',
            ],
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Islabelprinted',
        ]);

        $this->addColumn('is_next_manifest', [
            'header' => __('Is Under Current Manifest?'),
            'align' => 'center',
            'index' => 'c.is_next_manifest',
            //'index' => 'is_next_manifest',
            'type' => 'options',
            'options' => [
                1 => 'Yes',
                0 => 'No',
            ],
            // 'filter' => false,
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Iscurrentmanifest'
        ]);

        $this->addColumn('is_submitted_to_eparcel', [
            'header' => __('Submit Consigment/ Download Label'),
            'align' => 'center',
            'index' => 'is_submitted_to_eparcel',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Submitconsigment'
        ]);
        $this->addColumn('number_of_articles', [
            'header' => __('No. of Articles'),
            'align' => 'center',
            'index' => 'number_of_articles',
            'sortable' => false,
            'filter' => false,
        ]);

        $this->addColumn('add_date', [
            'header' => __('Created On'),
            'align' => 'center',
            'index' => 'add_date',
            'type' => 'datetime',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Date'
        ]);

        $this->addColumn('modify_date', [
            'header' => __('Modified On'),
            'align' => 'center',
            'index' => 'modify_date',
            'type' => 'datetime',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Date'
        ]);

        $this->addColumn('is_return_label_printed', [
            'header' => __('Is Return Labels Printed ?'),
            'align' => 'center',
            'index' => 'c.is_return_label_printed',
            //'index' => 'is_return_label_printed',
            'type' => 'options',
            'options' => [
                1 => 'Yes',
                0 => 'No',
            ],
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Consignment\Returnlabelprinted'
        ]);

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('order_consignment');
        // $this->setFilterVisibility(false);
        $this->getMassactionBlock()->setFormFieldName('order_consignment');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $shippingMethods = [];

        $auspostlabel = $this->auspostlabel->getCollection();

        if (sizeof($auspostlabel) > 0) {
            foreach ($auspostlabel as $item) {
                $shippingMethods[base64_encode($item->getChargeCode() . "<->" . $item->getType())] = $item->getType() . " [ " . $item->getChargeCode() . " ]";
            }
        }

        $this->getMassactionBlock()->addItem(
            'changeMethod',
            [
                'label' => __('Change Shipping Methods'),
                'url' => $this->getUrl('*/*/changeShippingMethod'),
                'additional' => [
                    'types' => [
                        'name' => 'change_method',
                        'type' => 'select',
                        'class' => 'required-entry change_method-ui',
                        'label' => __('Change Shipping Methods'),
                        'values' => $shippingMethods
                    ]
                ]
            ]
        );
        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Mass Generate Shipments'),
            'url' => $this->getUrl('*/*/massShipmentGenerate'),
            'confirm' => __('Are you sure you want to mass generate shipment and print mass label?')
        ]);

        $this->getMassactionBlock()->addItem('downloadmultiplelabels', [
            'label' => __('Mass Generate Labels'),
            'url' => $this->getUrl('*/*/massGenerateAndDownloadLabels'),
            'confirm' => __('Are you sure you want to mass generate label and download it?')
        ]);

        $this->getMassactionBlock()->addItem('assign', [
            'label' => __('Add to Current Manifest'),
            'url' => $this->getUrl('*/*/massAssignConsignment')
        ]);

        $this->getMassactionBlock()->addItem('unassign', [
            'label' => __('Remove from Current Manifest'),
            'url' => $this->getUrl('*/*/massUnassignConsignment'),
            'confirm' => __('Are you sure you want remove consignment from current manifest?')
        ]);

        // $this->getMassactionBlock()->addItem('delete', array(
        //     'label' => __('Delete Consignments'),
        //     'url' => $this->getUrl('*/*/massDeleteConsignment'),
        //     'confirm' => __('Are you sure you want to do delete consignment?')
        // ));


        // $this->getMassactionBlock()->addItem('downloadmultiplelabels1', array(
        //     'label' => __('Mass Generate Shipment for Return Labels'),
        //     'url' => $this->getUrl('*/*/massGenerateShipmentReturnLabels'),
        //     'confirm' => __('Are you sure you want to do mass label generate shipment for return labels?')
        // ));

        // $this->getMassactionBlock()->addItem('downloadreturnmultiplelabels', array(
        //     'label' => __('Mass Generate Return Labels'),
        //     'url' => $this->getUrl('*/*/massGenerateReturnLabels'),
        //     'confirm' => __('Are you sure you want to do mass label generate return labels ?')
        // ));


        $this->getMassactionBlock()->addItem('generatelabels', [
            'label' => __('Download Multiple Labels'),
            'url' => $this->getUrl('*/*/massDownloadLabels')
        ]);
        return $this;
    }
    public function getOptions()
    {
        $status = array();
        $display_choosen_status = $this->scopeConfig->getValue('carriers/ausposteParcel/displayChoosenStatus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($display_choosen_status == 1) {
            $chosen_statuses = $this->scopeConfig->getValue('carriers/ausposteParcel/chooseStatuses', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (!empty($chosen_statuses)) {
                $chosen_statuses = explode(',', $chosen_statuses);
                foreach ($chosen_statuses as $key => $value) {
                    if($value==null) continue;
                    $status[$value] = ucfirst($value);
                }
            } else {
                $status = array('processing'=>'Processing','pending'=>'Pending','complete'=>'Complete');
            }
        } else {
                $status = array('processing'=>'Processing','pending'=>'Pending','complete'=>'Complete');
        }
        return $status;
    }
}