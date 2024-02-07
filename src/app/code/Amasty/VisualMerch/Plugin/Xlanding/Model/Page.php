<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Xlanding\Model;

class Page
{

    /**
     * @param $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundGetAvailableStatuses($subject, callable $proceed)
    {
        if ($subject->isDynamic()) {
            return $proceed();
        }
        return [
            $subject::STATUS_ENABLED => __('Enabled'),
            $subject::STATUS_DISABLED => __('Disabled')
        ];
    }
}
