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

use Magento\Framework\Phrase;
use Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\QueueReport;
use Mageplaza\ZohoCRM\Model\Source\MagentoObject;

/**
 * Class Invoice
 * @package Mageplaza\ZohocRM\Block\Adminhtml\Sync\Edit\Tab\Report
 */
class Invoice extends QueueReport
{

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
        $this->getRequest()->setParam('magento_object', MagentoObject::INVOICE);
        $this->addZohoEntity($fieldset, $this->getCurrentInvoice());
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $html = '<section class="admin__page-section">
                    <div class="admin__page-section-title">
                        <span class="title">' . $this->getTabLabel() . '</span>
                    </div>';
        $html .= parent::_toHtml();
        $html .= '</section>';

        return $html;
    }

    /**
     * @return mixed
     */
    public function getCurrentInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }
}
