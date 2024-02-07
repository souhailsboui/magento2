<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Freeshipping\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{

/**
     * @return mixed
 */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array(
                    'data' => [
            'id' => 'edit_form',
                        'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
                    ]
                )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
