<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Block\Adminhtml\Rule;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ReindexNowButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $ruleId = $this->getRuleId();
        if ($ruleId) {
            $data = [
                'label'      => __('Reindex Now'),
                'class'      => 'delete',
                'on_click'   => 'setLocation(\'' .
                    $this->getUrlBuilder()->getUrl('*/*/reindex', ['id' => $ruleId]) .
                    '\')',
                'sort_order' => 30,
            ];
        }

        return $data;
    }
}
