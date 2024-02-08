<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace GlobalColours\Overrides\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Psr\Log\LoggerInterface;


/**
 * @inheritdoc
 */
class CustomerGroup implements ResolverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ValueFactory
     */
    private $valueFactory;


    /**
     * @param ValueFactory $valueFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ValueFactory $valueFactory,
        CustomerRepositoryInterface $customerRepository,
        GroupRepositoryInterface $groupRepository,
        LoggerInterface $logger,
    ) {
        $this->valueFactory = $valueFactory;
        $this->customerRepository = $customerRepository;
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value["email"])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        $result = function () use ($value, $context) {


            $customer = $this->customerRepository->get($value["email"]);

            $group = $this->groupRepository->getById($customer->getGroupId());

            if (!$group || !$group->getId()) {
                return null;
            }

            return $group->getCode();
        };

        return $this->valueFactory->create($result);
    }
}
