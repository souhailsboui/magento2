<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Articletype\Edit\Tab;

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
     * @param Context     $context
     * @param Registry    $registry
     * @param FormFactory $formFactory
     * @param Store       $systemStore
     * @param array       $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Article Type Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Article Type Information');
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
        $model = $this->_coreRegistry->registry('articletype_data');
        $isElementDisabled = false;
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('articletype_form', array('legend' => __('Article Type Information')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

        $fieldset->addField(
            'name',
            'text',
            array(
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'required' => true,
            'class' => 'required-entry validate-alpha',
                )
        );
        $fieldset->addField(
            'weight',
            'text',
            array(
            'name' => 'weight',
            'label' => __('Weight (Kgs)'),
            'title' => __('Weight (Kgs)'),
            'class' => 'required-entry validate-number validate-zero-or-greater validate-digits-range digits-range-01-22',
            'required' => true,
            'name' => 'weight'
                )
        );
        $fieldset->addField(
            'height',
            'text',
            array(
            'name' => 'height',
            'label' => __('Height (cm)'),
            'title' => __('Height (cm)'),
            'class' => 'required-entry validate-zero-or-greater validate-number validate-digits-range digits-range-01-105',
            'required' => true,
            'name' => 'height'
                )
        );
        $fieldset->addField(
            'width',
            'text',
            array(
            'name' => 'width',
            'label' => __('Width (cm)'),
            'title' => __('Width (cm)'),
            'class' => 'required-entry validate-zero-or-greater validate-number validate-digits-range digits-range-01-105',
            'required' => true,
            'name' => 'width'
                )
        );
        $fieldset->addField(
            'length',
            'text',
            array(
            'name' => 'length',
            'label' => __('Length (cm)'),
            'title' => __('Length (cm)'),
            'class' => 'required-entry validate-zero-or-greater validate-number validate-digits-range digits-range-01-105',
            'required' => true,
            'name' => 'length'
                )
        );
        $fieldset->addField(
            'status',
            'select',
            [
            'name' => 'status',
            'label' => __('Status'),
            'title' => __('Status'),
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
                ]
        );

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
     * @param  string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
