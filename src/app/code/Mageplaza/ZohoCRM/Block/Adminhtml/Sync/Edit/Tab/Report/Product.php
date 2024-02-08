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

use Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\QueueReport;
use Mageplaza\ZohoCRM\Model\Source\MagentoObject;

/**
 * Class Product
 * @package Mageplaza\ZohocRM\Block\Adminhtml\Sync\Edit\Tab\Report
 */
class Product extends QueueReport
{
    /**
     * @param $fieldset
     */
    public function addExtraFields($fieldset)
    {
        $this->getRequest()->setParam('magento_object', MagentoObject::PRODUCT);
        $this->addZohoEntity($fieldset, $this->getCurrentProduct());
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }
}
