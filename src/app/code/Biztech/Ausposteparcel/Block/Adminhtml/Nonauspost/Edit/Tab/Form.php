<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Nonauspost\Edit\Tab;

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
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        \Biztech\Ausposteparcel\Helper\Info $infoHelper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->infoHelper = $infoHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
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
        $model = $this->_coreRegistry->registry('nonauspost_data');
        $isElementDisabled = false;
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('articletype_form', array('legend' => __('Information')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

        $fieldset->addField(
            'method',
            'select',
            [
            'name' => 'method',
            'label' => __('Method'),
            'title' => __('Method'),
            'class' => 'required-entry',
            'required' => true,
            'values' => $this->infoHelper->getNonauspostShippingTypeOptions(),
                ]
        );
        $fieldset->addField(
            'charge_code',
            'select',
            [
            'name' => 'charge_code',
            'label' => __('Charge Code'),
            'title' => __('Charge Code'),
            'class' => 'required-entry',
            'required' => true,
            'values' => $this->infoHelper->getChargeCodeOptions(true),
                ]
        );

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
