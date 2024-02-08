<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Freeshipping;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Biztech\Ausposteparcel\Model\Mysql4\Freeshipping\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Biztech\Ausposteparcel\Model\Cresource\Freeshipping\Collection $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('freeshippingGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        //$this->setUseAjax(false);
    }

    protected function _prepareCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->ausposteparcelHelper = $objectManager->get('Biztech\Ausposteparcel\Helper\Data');
        $this->messageManager = $objectManager->get('Magento\Framework\Message\ManagerInterface');
        if (!empty($this->ausposteparcelHelper->getAllWebsites())) {
            $collection = $this->collectionFactory->load();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('ID'),
            'align' => 'right',
            'width' => '10px',
            'index' => 'id',
        ]);

        $this->addColumn('charge_code', [
            'header' => __('Charge Code'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'charge_code',
        ]);

        $this->addColumn('from_amount', [
            'header' => __('Cost Range'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'from_amount',
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Freeshipping\Range',
            'filter' => false,
            'sortable' => false
        ]);

        $this->addColumn('minimum_amount', [
            'header' => __('Shipping Cost'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'minimum_amount',
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Freeshipping\Shippingcost',
        ]);

        $this->addColumn('status', [
            'header' => __('Status'),
            'align' => 'center',
            'width' => '30px',
            'index' => 'status',
            'type' => 'options',
            'options' => [
                1 => 'Enabled',
                2 => 'Disabled',
            ],
        ]);

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
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('freeshipping');

        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure you want to delete?')
        ]);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $statuses = $objectManager->create('Magento\Catalog\Model\Product\Attribute\Source\Status')->getOptionArray();

        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem('status', [
            'label' => __('Change status'),
            'url' => $this->getUrl('*/*/massStatus', ['_current' => true]),
            'additional' => [
                'visibility' => [
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Status'),
                    'values' => $statuses
                ]
            ]
        ]);

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
