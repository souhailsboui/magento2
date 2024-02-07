<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderAmountGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderAmountGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class GetOrderAmount
 *
 * @package Bss\OrderAmountGraphQl\Model\Resolver
 */
class GetOrderAmount implements ResolverInterface {

    /**
     * @var \Bss\OrderAmount\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * GetOrderAmount constructor.
     * @param \Bss\OrderAmount\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Bss\OrderAmount\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->helper =$helper;
        $this->customerSession = $customerSession;
    }

    /**
     * Get data order amount by customer group
     *
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $result = [];
        $customerGroup = $args['customer_group'] ? $args['customer_group'] : $this->customerSession->getCustomerGroupId();
        $result['customer_group'] = $customerGroup;
        $result['minimum_amount'] = $this->helper->getAmoutDataForCustomerGroup($customerGroup);
        $result['message'] = $this->helper->getMessage($customerGroup);
        return $result;
    }
}
