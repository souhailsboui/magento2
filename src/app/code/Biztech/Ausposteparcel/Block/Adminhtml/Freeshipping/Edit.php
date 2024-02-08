<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Freeshipping;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * CMS block edit form container
 */
class Edit extends Container
{

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('freeshipping_data') && $this->_coreRegistry->registry('freeshipping_data')->getId()) {
            return __("Edit Rule", $this->escapeHtml($this->_coreRegistry->registry('freeshipping_data')->getTitle()));
        } else {
            return __('Create Rule');
        }
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_controller = 'adminhtml_freeshipping';
        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Rule'));
        $this->buttonList->update('delete', 'label', __('Delete Rule'));

        $this->buttonList->add(
            'saveandcontinue',
            array(
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => array(
                'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
            )
                ),
            -100
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('freeshipping_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'freeshipping_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'freeshipping_content');
                }
            }
        ";
    }
}
