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
namespace Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\Grid\Render;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Mageplaza\ZohoCRM\Model\Source\QueueStatus;

/**
 * Class Status
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\Grid\Render
 */
class Status extends AbstractRenderer
{
    /**
     * @param DataObject $row
     *
     * @return  string
     */
    public function render(DataObject $row)
    {
        $class = '';
        $text  = '';
        switch ($this->_getValue($row)) {
            case QueueStatus::ERROR:
                $class = 'grid-severity-critical';
                $text  = __('Error');
                break;
            case QueueStatus::SUCCESS:
                $class = 'grid-severity-notice';
                $text  = __('Success');
                break;
            case QueueStatus::PENDING:
                $class = 'grid-severity-minor';
                $text  = __('Pending');
                break;
        }

        return '<span class="' . $class . '"><span>' . $text . '</span></span>';
    }
}
