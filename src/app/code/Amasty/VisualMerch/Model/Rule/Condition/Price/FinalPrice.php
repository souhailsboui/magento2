<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */
namespace Amasty\VisualMerch\Model\Rule\Condition\Price;

class FinalPrice extends AbstractPrice
{
    public function getAttributeElementHtml()
    {
        return __('Final Price');
    }

    protected function _getAttributeCode()
    {
        return 'final_price';
    }
}
