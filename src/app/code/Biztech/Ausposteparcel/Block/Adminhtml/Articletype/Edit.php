<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Articletype;

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
        if ($this->_coreRegistry->registry('articletype_data')->getId()) {
            return __("Edit Article Type '%1'", $this->escapeHtml($this->_coreRegistry->registry('articletype_data')->getTitle()));
        } else {
            return __('Add New Article Type');
        }
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_controller = 'adminhtml_articletype';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->update('delete', 'label', __('Delete'));

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
                if (tinyMCE.getInstanceById('articletype_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'articletype_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'articletype_content');
                }
            }
        ";
    }
}
