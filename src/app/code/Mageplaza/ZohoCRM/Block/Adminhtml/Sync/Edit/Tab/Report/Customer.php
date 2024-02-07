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
namespace Mageplaza\ZohocRM\Block\Adminhtml\Sync\Edit\Tab\Report;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\QueueReport;
use Mageplaza\ZohoCRM\Model\ResourceModel\Queue\CollectionFactory as QueueCollection;
use Mageplaza\ZohoCRM\Model\Source\MagentoObject;

/**
 * Class Customer
 * @package Mageplaza\ZohocRM\Block\Adminhtml\Sync\Edit\Tab\Report
 */
class Customer extends QueueReport
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Customer constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param QueueCollection $queueCollection
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        QueueCollection $queueCollection,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $registry, $formFactory, $queueCollection, $data);
    }

    /**
     * @return Phrase|string
     */
    public function getTabLabel()
    {
        return __('Zoho CRM');
    }

    /**
     * @param $fieldset
     */
    public function addExtraFields($fieldset)
    {
        $id       = $this->getRequest()->getParam('id');
        $customer = $this->customerFactory->create()->load($id);
        $this->getRequest()->setParam('magento_object', MagentoObject::CUSTOMER);
        $this->addZohoEntity($fieldset, $customer);
        if ($customer->getZohoLeadEntity()) {
            $fieldset->addField('zoho_lead_entity', 'note', [
                'label' => __('Zoho Lead Entity'),
                'text'  => $customer->getZohoLeadEntity()
            ]);
        }
        if ($customer->getZohoContactEntity()) {
            $fieldset->addField('zoho_contact_entity', 'note', [
                'label' => __('Zoho Contact Entity'),
                'text'  => $customer->getZohoContactEntity()
            ]);
        }
    }

    /**
     * @return bool|mixed
     */
    public function canShowTab()
    {
        return $this->getRequest()->getParam('id');
    }
}
