<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Articletype;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    protected $_collectionFactory;
    protected $messageManager;
    protected $ausposteparcelHelper;
    protected $statuses;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Biztech\Ausposteparcel\Helper\Data $ausposteparcelHelper,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Biztech\Ausposteparcel\Model\Cresource\Articletype\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        $this->ausposteparcelHelper = $ausposteparcelHelper;
        $this->moduleManager = $moduleManager;
        $this->statuses = $status->getOptionArray();
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('biztech_ausposteparcel/articletype/index', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'biztech_ausposteparcel/articletype/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('articletypeGrid');
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
            if (!empty($this->ausposteparcelHelper->getAllWebsites())) {
                $collection = $this->_collectionFactory->load();
                $this->setCollection($collection);
                return parent::_prepareCollection();
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return $this;
        }
    }
    
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
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
            'name',
            [
            'header' => __('Article Types'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'name',
            'class' => 'name',
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Articletype\Name',
            'sortable' => true
                ]
        );
        $this->addColumn(
            'status',
            [
            'header' => __('Status'),
            'align' => 'center',
            'width' => '30px',
            'index' => 'status',
            'type' => 'options',
            'options' => [
                1 => 'Enabled',
                2 => 'Disabled',
            ],
            'sortable' => false
                ]
        );

        $this->addColumn('action', [
            'header' => __('Action'),
            'width' => '30px',
            'align' => 'center',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => [
                [
                    'caption' => __('Edit'),
                    'url' => ['base' => '*/*/edit'],
                    'field' => 'id'
                ]
            ],
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
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
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('articletype');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure you want to delete?')
                ]
        );

        array_unshift($this->statuses, ['label' => '', 'value' => '']);

        $this->getMassactionBlock()->addItem('status', [
            'label' => __('Change status'),
            'url' => $this->getUrl('*/*/massStatus', ['_current' => true]),
            'additional' => [
                'visibility' => [
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Status'),
                    'values' => $this->statuses
                ]
            ]
        ]);
        return $this;
    }
}
