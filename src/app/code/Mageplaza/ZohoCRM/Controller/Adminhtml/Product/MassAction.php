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

namespace Mageplaza\ZohoCRM\Controller\Adminhtml\Product;

use Mageplaza\ZohoCRM\Controller\Adminhtml\AbstractMassAction;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;

/**
 * Class MassAction
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Product
 */
class MassAction extends AbstractMassAction
{
    /**
     * @return string
     */
    public function getType()
    {
        return ZohoModule::PRODUCT;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->productCollectionFactory->create();
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return 'catalog/product';
    }
}
