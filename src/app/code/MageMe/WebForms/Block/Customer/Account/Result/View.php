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

namespace MageMe\WebForms\Block\Customer\Account\Result;

use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class View extends Template
{
    /**
     * @var bool
     */
    protected $_isScopePrivate = true;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry         $registry,
        Template\Context $context,
        array            $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * @return ResultInterface|null
     */
    public function getResult(): ?ResultInterface
    {
        return $this->registry->registry('webforms_result');
    }

    /**
     * @return string
     */
    public function getMessageForm(): string
    {
        /** @var MessageForm $block */
        $block = $this->getChildBlock('webforms.customer.account.result.view.message_form');
        $block->setResult($this->getResult());
        return $block->toHtml();
    }

    /**
     * @return string
     */
    public function getMessages(): string
    {
        /** @var Messages $block */
        $block = $this->getChildBlock('webforms.customer.account.result.view.messages');
        $block->setResult($this->getResult());
        return $block->toHtml();
    }
}
