<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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
namespace Mageplaza\ZohocRM\Block\Adminhtml\Sync\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\ZohoCRM\Block\Adminhtml\Render\Queue;
use Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\Grid\Queue as QueueGrid;
use Mageplaza\ZohoCRM\Model\ResourceModel\Queue\CollectionFactory as QueueCollection;
use Mageplaza\ZohoCRM\Model\Source\QueueStatus;

/**
 * Class QueueReport
 * @package Mageplaza\ZohocRM\Block\Adminhtml\Sync\Edit\Tab
 */
class QueueReport extends Generic implements TabInterface
{
    /**
     * @var QueueCollection
     */
    protected $queueCollection;

    /**
     * QueueReport constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param QueueCollection $queueCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        QueueCollection $queueCollection,
        array $data = []
    ) {
        $this->queueCollection = $queueCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form     = $this->_formFactory->create();
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => $this->getLegendLabel()
        ]);

        $this->addExtraFields($fieldset);

        $form->addField('queues', Queue::class, [
            'queue_data' => $this->getLayout()->createBlock(QueueGrid::class)->toHtml()
        ]);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return Phrase
     */
    public function getLegendLabel()
    {
        return __('Queue Report');
    }

    /**
     * @param $fieldset
     */
    public function addExtraFields($fieldset)
    {
        $id             = $this->getRequest()->getParam('id');
        $pendingRequest = $this->queueCollection->create()->getTotalRequest($id, QueueStatus::PENDING);
        $fieldset->addField('pending_request', 'note', [
            'label' => __('Pending Requests'),
            'text'  => $pendingRequest
        ]);

        $totalObject = $this->queueCollection->create()->getTotalRequest($id);
        $fieldset->addField('total_object', 'note', [
            'label' => __('Total Objects'),
            'text'  => $totalObject
        ]);

        $totalRequest = $this->queueCollection->create()
            ->getTotalRequest($id, [QueueStatus::SUCCESS, QueueStatus::ERROR]);
        $fieldset->addField('total_request', 'note', [
            'label' => __('Total Requests'),
            'text'  => $totalRequest,
        ]);
    }

    /**
     * @param $fieldset
     * @param $model
     */
    public function addZohoEntity($fieldset, $model)
    {
        if ($model && $model->getZohoEntity()) {
            $fieldset->addField('zoho_entity', 'note', [
                'label' => __('Zoho Entity'),
                'text'  => $model->getZohoEntity()
            ]);
        }
    }

    /**
     * @return Phrase|string
     */
    public function getTabLabel()
    {
        return __('Queue Report');
    }

    /**
     * @return Phrase|string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function isAjaxLoaded()
    {
        return '';
    }
}
