<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Nonauspost;

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
        if ($this->_coreRegistry->registry('nonauspost_data')->getId()) {
            return __("Edit '%1'", $this->escapeHtml($this->_coreRegistry->registry('nonauspost_data')->getTitle()));
        } else {
            return __('Add');
        }
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_controller = 'adminhtml_nonauspost';

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
                    tinyMCE.execCommand('mceAddControl', false, 'nonauspost_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'nonauspost_content');
                }
            }
        ";
    }
}
