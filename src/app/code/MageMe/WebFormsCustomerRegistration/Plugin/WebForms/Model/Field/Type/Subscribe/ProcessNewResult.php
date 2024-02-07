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

namespace MageMe\WebFormsCustomerRegistration\Plugin\WebForms\Model\Field\Type\Subscribe;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Model\Field\Type\Subscribe;
use Magento\Customer\Model\SessionFactory;
use Magento\Newsletter\Model\SubscriberFactory;

class ProcessNewResult
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    public function __construct(
        SubscriberFactory $subscriberFactory,
        SessionFactory    $sessionFactory
    )
    {
        $this->subscriberFactory = $subscriberFactory;
        $this->sessionFactory    = $sessionFactory;
    }

    public function afterProcessNewResult(Subscribe $field, FieldInterface $output)
    {
        $customer = $this->sessionFactory->create()->getCustomer();
        if ($customer->getId()) {
            $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($customer->getEmail(), $customer->getWebsiteId());
            if ($subscriber->getId()) {
                $subscriber->setCustomerId($customer->getId())->save();
            }
        }
        return $output;
    }
}