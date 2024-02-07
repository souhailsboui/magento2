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
namespace Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit;

use Exception;
use Magento\Backend\Block\Widget\Tabs as CoreTabs;

/**
 * Class Tabs
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\Code\Edit
 */
class Tabs extends CoreTabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sync_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Synchronization Information'));
    }

    /**
     * @return CoreTabs|void
     * @throws Exception
     */
    protected function _beforeToHtml()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $this->addTab('queue_report', 'mageplaza_zoho_sync_edit_tab_queue_report');
        }

        parent::_beforeToHtml();
    }
}
