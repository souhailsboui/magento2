<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebFormsZoho\Config\Options\Crm;

use Exception;
use MageMe\WebFormsZoho\Helper\ZohoHelper;
use Magento\Framework\Data\OptionSourceInterface;

class LeadSources implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;
    /**
     * @var ZohoHelper
     */
    private $zohoHelper;

    /**
     * @param ZohoHelper $zohoHelper
     */
    public function __construct(ZohoHelper $zohoHelper)
    {
        $this->zohoHelper = $zohoHelper;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        if ($this->options) {
            return $this->options;
        }
        try {
            $leadFields = $this->zohoHelper->getApi()->CRM()->getLeadFields();
            foreach ($leadFields as $leadField) {
                if ($leadField['api_name'] == 'Lead_Source') {
                    foreach ($leadField['pick_list_values'] as $value) {
                        $this->options[] = [
                            'label' => __($value['display_value']),
                            'value' => $value['actual_value']
                        ];
                    }
                    break;
                }
            }
        } catch (Exception $exception) {
            return [];
        }
        return $this->options;

    }
}