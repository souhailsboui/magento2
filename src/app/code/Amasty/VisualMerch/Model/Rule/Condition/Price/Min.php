<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */
namespace Amasty\VisualMerch\Model\Rule\Condition\Price;

class Min extends AbstractPrice
{
    public function getAttributeElementHtml()
    {
        return __('Min Price');
    }

    protected function _getAttributeCode()
    {
        return 'min_price';
    }
}
