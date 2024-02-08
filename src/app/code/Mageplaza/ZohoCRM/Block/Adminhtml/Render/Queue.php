<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\ZohoCRM\Block\Adminhtml\Render;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Queue
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\Render
 */
class Queue extends AbstractElement
{
    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getData('queue_data');
    }
}
