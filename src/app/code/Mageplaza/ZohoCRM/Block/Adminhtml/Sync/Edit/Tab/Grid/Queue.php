<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\Grid;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\DataObject;
use Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\Grid\Render\QueueObject;
use Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\Grid\Render\Status;
use Mageplaza\ZohoCRM\Model\ResourceModel\Queue\CollectionFactory as QueueCollection;
use Mageplaza\ZohoCRM\Model\Source\MagentoObject;
use Mageplaza\ZohoCRM\Model\Source\QueueActions;
use Mageplaza\ZohoCRM\Model\Source\QueueStatus;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;

/**
 * Class Queue
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\Grid
 */
class Queue extends Extended
{
    /**
     * @var QueueCollection
     */
    protected $queueCollection;

    /**
     * Queue constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param QueueCollection $queueCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        QueueCollection $queueCollection,
        array $data = []
    ) {
        $this->queueCollection = $queueCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('queueGrid');
        $this->setDefaultSort('queue_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->queueCollection->create();

        $magentoObject = $this->getRequest()->getParam('magento_object');

        if ($magentoObject) {
            $id = $this->getRequest()->getParam('id');
            if ($magentoObject === MagentoObject::ORDER) {
                $id = $this->getRequest()->getParam('order_id');
            }

            if ($magentoObject === MagentoObject::INVOICE) {
                $id = $this->getRequest()->getParam('invoice_id');
            }

            $collection->addFieldToFilter('object', $id)
                ->addFieldToFilter('magento_object', $magentoObject);
        } else {
            $collection->addFieldToFilter('sync_id', $this->getRequest()->getParam('id'));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('queue_id', [
            'header'   => __('ID'),
            'index'    => 'queue_id',
            'sortable' => true,
            'type'     => 'number',
        ]);
        $fullActionName = $this->getRequest()->getFullActionName();
        if ($fullActionName === 'mpzoho_sync_edit' || $fullActionName === 'mpzoho_sync_queueGrid') {
            $this->addColumn('object', [
                'header'   => __('Object'),
                'index'    => 'object',
                'filter'   => false,
                'renderer' => QueueObject::class,
                'options'  => QueueStatus::getOptionArray()
            ]);
        }

        $this->addColumn('status', [
            'header'   => __('Status'),
            'index'    => 'status',
            'renderer' => Status::class,
            'options'  => QueueStatus::getOptionArray(),
            'type'     => 'options',
        ]);

        if ($fullActionName === 'customer_index_edit') {
            $this->addColumn('zoho_module', [
                'header'  => __('Zoho Module'),
                'index'   => 'zoho_module',
                'options' => ZohoModule::getOptionArray(),
                'type'    => 'options',
            ]);
        }

        $this->addColumn('action', [
            'header'  => __('Events'),
            'index'   => 'action',
            'type'    => 'options',
            'options' => QueueActions::getOptionArray()
        ]);
        $this->addColumn('created_at', [
            'header' => __('Created On'),
            'index'  => 'created_at',
            'type'   => 'datetime',
        ]);
        $this->addColumn('updated_at', [
            'header' => __('Updated On'),
            'index'  => 'updated_at',
            'type'   => 'datetime',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpzoho/sync/queueGrid', ['_current' => true]);
    }

    /**
     * Return null to disable cursor pointer.
     *
     * @param DataObject $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }
}
