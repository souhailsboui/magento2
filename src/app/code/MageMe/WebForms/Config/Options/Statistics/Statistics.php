<?php

namespace MageMe\WebForms\Config\Options\Statistics;

use MageMe\WebForms\Helper\Statistics\FormStat;
use Magento\Framework\Data\OptionSourceInterface;

class Statistics implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('All'),
                'value' => FormStat::RESULT_ALL,
            ],
            [
                'label' => __('Unread'),
                'value' => FormStat::RESULT_UNREAD,
            ],
            [
                'label' => __('Replied'),
                'value' => FormStat::RESULT_REPLIED,
            ],
            [
                'label' => __('Follow Up'),
                'value' => FormStat::RESULT_FOLLOW_UP,
            ],
            [
                'label' => __('Not Approved'),
                'value' => FormStat::RESULT_STATUS_NOT_APPROVED,
            ],
            [
                'label' => __('Pending'),
                'value' => FormStat::RESULT_STATUS_PENDING,
            ],
            [
                'label' => __('Approved'),
                'value' => FormStat::RESULT_STATUS_APPROVED,
            ],
            [
                'label' => __('Completed'),
                'value' => FormStat::RESULT_STATUS_COMPLETED,
            ],
        ];
    }

}