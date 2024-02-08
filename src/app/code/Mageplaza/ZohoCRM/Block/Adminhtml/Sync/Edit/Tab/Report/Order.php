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
 * Class Order
 * @package Mageplaza\ZohocRM\Block\Adminhtml\Sync\Edit\Tab\Report
 */
class Order extends QueueReport
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
        $this->getRequest()->setParam('magento_object', MagentoObject::ORDER);
        $this->addZohoEntity($fieldset, $this->getCurrentOrder());
    }

    /**
     * @return mixed
     */
    public function getCurrentOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
}
