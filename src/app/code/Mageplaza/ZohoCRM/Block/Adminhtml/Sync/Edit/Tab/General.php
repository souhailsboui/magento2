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
use Magento\Config\Model\Config\Source\Website;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\ZohoCRM\Model\Source\MagentoObject;
use Mageplaza\ZohoCRM\Model\Source\Status;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;

/**
 * Class General
 * @package Mageplaza\GiftCard\Block\Adminhtml\Sync\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var ZohoModule
     */
    protected $zohoModule;

    /**
     * @var MagentoObject
     */
    protected $magentoObject;

    /**
     * @var Website
     */
    protected $website;

    /**
     * @var Status
     */
    protected $status;

    /**
     * General constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ZohoModule $zohoModule
     * @param MagentoObject $magentoObject
     * @param Website $website
     * @param Status $status
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ZohoModule $zohoModule,
        MagentoObject $magentoObject,
        Website $website,
        Status $status,
        array $data = []
    ) {
        $this->zohoModule    = $zohoModule;
        $this->magentoObject = $magentoObject;
        $this->website       = $website;
        $this->status        = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $form->setFieldNameSuffix('sync');
        $sync = $this->_coreRegistry->registry('sync_rule');

        $fieldset = $form->addFieldset('general', [
            'legend' => __('Please select')
        ]);
        if ($sync->getId()) {
            $fieldset->addField('sync_id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField('name', 'text', [
            'name'     => 'name',
            'required' => true,
            'label'    => __('Name'),
            'title'    => __('Name'),
        ]);
        $fieldset->addField('status', 'select', [
            'name'     => 'status',
            'required' => true,
            'label'    => __('Status'),
            'title'    => __('Status'),
            'values'   => $this->status->toOptionArray()
        ]);
        $fieldset->addField('magento_object', 'select', [
            'name'     => 'magento_object',
            'title'    => __('Magento Object'),
            'label'    => __('Magento Object'),
            'required' => true,
            'values'   => $this->magentoObject->toOptionArray()
        ]);
        $fieldset->addField('zoho_module', 'select', [
            'name'     => 'zoho_module',
            'title'    => __('Zoho Module'),
            'label'    => __('Zoho Module'),
            'required' => true,
            'values'   => $this->zohoModule->toOptionArray()
        ]);
        $fieldset->addField('website_ids', 'multiselect', [
            'name'     => 'website_ids',
            'title'    => __('Website'),
            'label'    => __('Website'),
            'required' => true,
            'values'   => $this->website->toOptionArray()
        ]);

        $fieldset->addField('priority', 'text', [
            'name'  => 'priority',
            'label' => __('Priority'),
            'title' => __('Priority'),
            'class' => 'validate-number validate-zero-or-greater',
            'note'  => __('If several rule meet the condition, the one with the highest priority will be applied. Smaller number means higher priority.')
        ]);

        $form->setValues($sync->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return Phrase|string
     */
    public function getTabLabel()
    {
        return __('Sync information');
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
}
