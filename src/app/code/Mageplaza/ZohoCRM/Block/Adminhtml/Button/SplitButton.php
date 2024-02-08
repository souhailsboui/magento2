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

use Magento\Ui\Component\Control\SplitButton as CoreSplitButton;

/**
 * Class SplitButton
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\Button
 */
class SplitButton extends CoreSplitButton
{
    /**
     * Retrieve button attributes html
     *
     * @return string
     */
    public function getButtonAttributesHtml()
    {
        $classes = ['action-default', 'action-secondary'];

        $title = $this->getLabel();

        $attributes = [
            'id'    => $this->getId() . '-button',
            'title' => $title,
            'class' => implode(' ', $classes),
        ];

        return $this->attributesToHtml($attributes);
    }

    /**
     * Retrieve toggle button attributes html
     *
     * @return string
     */
    public function getToggleAttributesHtml()
    {
        $classes    = ['action-toggle', 'action-secondary'];
        $title      = $this->getLabel();
        $attributes = ['title' => $title, 'class' => implode(' ', $classes)];
        $this->getDataAttributes(['mage-init' => '{"dropdown": {}}', 'toggle' => 'dropdown'], $attributes);

        return $this->attributesToHtml($attributes);
    }
}
