<?php

namespace Meetanshi\ShippingRules\Block\Adminhtml\Rule;

use Magento\Backend\Block\Widget\Context as Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Meetanshi\ShippingRules\Model\ResourceModel\Rule\CollectionFactory;
use Meetanshi\ShippingRules\Helper\Data;

class Grid extends Extended
{
    protected $ruleCollectionFactory;
    protected $helper;

    public function __construct(CollectionFactory $ruleCollectionFactory, Data $helper, Context $context, BackendHelper $backendHelper)
    {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->helper = $helper;

        parent::__construct($context, $backendHelper);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('rulesGrid');
        $this->setDefaultSort('position');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('shippingrules/rule/edit', ['id' => $row->getId()]);
    }

    protected function _prepareCollection()
    {
        $collection = $this->ruleCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', [
            'header' => __('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'rule_id',
        ]);

        $this->addColumn('name', [
            'header' => __('Rule Name'),
            'index' => 'name',
        ]);

        $this->addColumn('shipping_methods', [
            'header' => __('Shipping Methods'),
            'align' => 'left',
            'width' => '80px',
            'renderer' => '\Meetanshi\ShippingRules\Block\Adminhtml\Rule\Grid\Renderer\Methods',
            'index' => 'shipping_methods',
        ]);

        $this->addColumn('calculation', [
            'header' => __('Rate Calculation Type'),
            'index' => 'calculation',
            'type' => 'options',
            'options' => $this->helper->getCalculations(),
        ]);

        $this->addColumn('rate_base', [
            'header' => __('Shipping Rate per the Order'),
            'index' => 'rate_base',
        ]);

        $this->addColumn('rate_fixed', [
            'header' => __('Fixed Shipping Rate per Product'),
            'index' => 'rate_fixed',
        ]);

        $this->addColumn('rate_percent', [
            'header' => __('Percentage Shipping Rate per Product'),
            'index' => 'rate_percent',
        ]);

        $this->addColumn('handling_fee', [
            'header' => __('Handling Shipping Rate in Percentage'),
            'index' => 'handling_fee',
        ]);

        $this->addColumn('position', [
            'header' => __('Priority'),
            'index' => 'position',
        ]);

        $this->addColumn('is_active', [
            'header' => __('Status'),
            'align' => 'left',
            'width' => '80px',
            'renderer' => '\Meetanshi\ShippingRules\Block\Adminhtml\Rule\Grid\Renderer\Color',
            'index' => 'is_active',
            'type' => 'options',
            'options' => $this->helper->getStatuses(),
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rules');

        $actions = [
            'massactivate' => 'Activate',
            'massinactivate' => 'Inactivate',
            'massdelete' => 'Delete',
        ];

        foreach ($actions as $code => $label) {
            $this->getMassactionBlock()->addItem($code, [
                'label' => __($label),
                'url' => $this->getUrl('*/*/' . $code),
                'confirm' => ($code == 'delete' ? __('Are you sure?') : null),
            ]);
        }
        return $this;
    }
}
