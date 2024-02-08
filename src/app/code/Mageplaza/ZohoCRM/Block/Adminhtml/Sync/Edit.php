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
namespace Mageplaza\ZohoCRM\Block\Adminhtml\Sync;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Edit
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\Sync
 */
class Edit extends Container
{
    protected function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'Mageplaza_ZohoCRM';
        $this->_controller = 'adminhtml_sync';
        parent::_construct();

        $this->addButton(
            'sync-next',
            [
                'label' => __('Next'),
                'class' => 'primary',
            ],
            1
        );

        $this->buttonList->update('back', 'label', __('Back To Grid'));

        $this->buttonList->add(
            'save_and_continue',
            [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]
                ]
            ],
            -100
        );
    }
}
