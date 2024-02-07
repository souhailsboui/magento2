<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_StoreCredit
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\StoreCredit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Mageplaza\StoreCredit\Helper\Data;
use Mageplaza\StoreCredit\Model\Action\ActionInterface;
use Mageplaza\StoreCredit\Model\Config\Source\Status;

/**
 * Class Action
 * @package Mageplaza\StoreCredit\Model
 */
abstract class Action extends DataObject implements ActionInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var null | \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var null | DataObject
     */
    protected $actionObject;

    /**
     * Action constructor.
     *
     * @param Data $helper
     * @param null $customer
     * @param null $actionObject
     * @param array $data
     */
    public function __construct(
        Data $helper,
        $customer = null,
        $actionObject = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->customer = $customer;
        $this->actionObject = $actionObject;

        parent::__construct($data);
    }

    /**
     * @inheritdoc
     */
    public function prepareTransaction()
    {
        $customer = $this->getCustomer();
        $actionObj = $this->getActionObject();

        return [
            'customer_id' => $customer->getId(),
            'title' => $this->getComment($this->getTitle(), $actionObj->getData('increment_id')),
            'amount' => $this->getAmount(),
            'status' => $this->getStatus(),
            'order_id' => $actionObj->getData('order_id'),
            'customer_note' => $actionObj->getData('customer_note'),
            'admin_note' => $actionObj->getData('admin_note')
        ];
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getAmount()
    {
        $amount = $this->getActionObject()->getData('amount');
        if (!$amount) {
            throw new LocalizedException(__('Cannot create transaction. Amount is invalid.'));
        }

        return (float)$amount;
    }

    /**
     * @return int
     */
    protected function getStatus()
    {
        return Status::COMPLETE;
    }

    /**
     * @param string $title
     * @param $incrementId
     *
     * @return Phrase|string
     */
    public function getComment($title, $incrementId)
    {
        return $incrementId ? __($title, $incrementId) : __($title);
    }

    /**
     * @return \Magento\Customer\Model\Customer|null
     */
    protected function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return DataObject|null
     */
    protected function getActionObject()
    {
        return $this->actionObject;
    }
}
