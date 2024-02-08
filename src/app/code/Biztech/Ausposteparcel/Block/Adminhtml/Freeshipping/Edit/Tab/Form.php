<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Freeshipping\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Form extends Generic implements TabInterface
{

/**
     * @var Store
 */
    protected $_systemStore;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param array $data
     */
    protected $ausposteParcelInfoHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->registry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('freeshipping_data');
        $isElementDisabled = false;
        $form = $this->_formFactory->create();
        
        $fieldset = $form->addFieldset('freeshipping_form', array('legend' => __('Information')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }
        
        $fieldset->addField('charge_code', 'select', array(
            'label' => __('Charge Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'charge_code',
            'values' => $this->ausposteParcelInfoHelper->getChargeCodeOptions(),
        ));

        $fieldset->addField('from_amount', 'text', array(
            'label' => __('From Cost'),
            'class' => 'required-entry  validate-number',
            'required' => true,
            'name' => 'from_amount'
        ));

        $fieldset->addField('to_amount', 'text', array(
            'label' => __('To Cost'),
            'class' => 'validate-number',
            'name' => 'to_amount',
            'note' => __('If this value is zero or empty, then cost range will be assumed as greater than or equal to from cost.'),
        ));

        $fieldset->addField('minimum_amount', 'text', array(
            'label' => __('Shipping Cost'),
            'class' => 'required-entry  validate-number',
            'required' => true,
            'name' => 'minimum_amount'
        ));

        $fieldset->addField('status', 'select', array(
            'label' => __('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => __('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => __('Disabled'),
                ),
            ),
        ));

        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        if ($model) {
            $form->setValues($model->getData());
            $this->setForm($form);
        }
       
        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
