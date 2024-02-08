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

namespace Mageplaza\ZohoCRM\Block\Adminhtml\Button;

/**
 * Class Product
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\Button
 */
class Product extends AbstractButton
{
    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return string
     */
    public function getPathUrl()
    {
        return 'mpzoho/product/add';
    }
}
