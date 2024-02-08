<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */
namespace Amasty\VisualMerch\Model\Rule\Condition\Price;

class Max extends AbstractPrice
{
    public function getAttributeElementHtml()
    {
        return __('Max Price');
    }

    protected function _getAttributeCode()
    {
        return 'max_price';
    }
}
