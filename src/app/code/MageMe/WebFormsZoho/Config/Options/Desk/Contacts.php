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

namespace MageMe\WebFormsZoho\Config\Options\Desk;

use Exception;
use MageMe\WebFormsZoho\Helper\ZohoHelper;
use Magento\Framework\Data\OptionSourceInterface;

class Contacts implements OptionSourceInterface
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
            $contacts = $this->zohoHelper->getApi()->Desk()->getContacts();
            foreach ($contacts as $contact) {
                $label = [];
                $label[] = $contact['lastName'];
                if (!empty($contact['firstName'])) {
                    $label[] = $contact['firstName'];
                }
                if (!empty($contact['email'])) {
                    $label[] = $contact['email'];
                }
                $this->options[] = [
                    'label' => implode(' ', $label),
                    'value' => $contact['id']
                ];
            }
        } catch (Exception $exception) {
            return [];
        }
        return $this->options;

    }
}