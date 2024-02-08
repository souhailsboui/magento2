<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Nonauspost;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    protected $_collectionFactory;
    protected $infoHelper;
    protected $messageManager;
    protected $ausposteparcelHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Biztech\Ausposteparcel\Helper\Data $ausposteparcelHelper,
        \Magento\Framework\Message\ManagerInterface $messageinterface,
        \Biztech\Ausposteparcel\Model\Cresource\Nonauspost\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Biztech\Ausposteparcel\Helper\Info $infoHelper,
        array $data = []
    ) {
        $this->messageManager = $messageinterface;
        $this->ausposteparcelHelper = $ausposteparcelHelper;
        $this->_collectionFactory = $collectionFactory;
        $this->moduleManager = $moduleManager;
        $this->infoHelper = $infoHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('biztech_ausposteparcel/nonauspost/index', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'biztech_ausposteparcel/nonauspost/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('nonauspostGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        try {
            if ($this->ausposteparcelHelper->isEnabled()) {
                $collection = $this->_collectionFactory->load();
                $this->setCollection($collection);
                return parent::_prepareCollection();
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return $this;
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
            'header' => __('ID'),
            'index' => 'id',
            'class' => 'id',
            'type' => 'number',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
            'align' => 'right',
            'width' => '10px',
                ]
        );
        $this->addColumn(
            'method',
            [
            'header' => __('Shipping Method'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'method',
            'type' => 'options',
            'options' => $this->infoHelper->getNonauspostShippingTypes(),
            'class' => 'method'
                ]
        );
        $this->addColumn(
            'charge_code',
            [
            'header' => __('Charge Code'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'charge_code',
            'type' => 'options',
            'options' => $this->infoHelper->getChargeCodeValues(true),
            'class' => 'charge_code'
                ]
        );

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '30px',
            'align' => 'center',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

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
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('nonauspost');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure you want to delete?')
                )
        );
        return $this;
    }
}
