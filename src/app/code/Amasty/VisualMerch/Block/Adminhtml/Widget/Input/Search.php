<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Block\Adminhtml\Widget\Input;

class Search extends \Magento\Backend\Block\Widget
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_VisualMerchUi::widget/input.phtml');
    }
}
