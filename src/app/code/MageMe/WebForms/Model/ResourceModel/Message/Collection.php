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

namespace MageMe\WebForms\Model\ResourceModel\Message;


use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Helper\HtmlHelper;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use MageMe\WebForms\Model\Message;
use MageMe\WebForms\Model\ResourceModel\AbstractCollection;
use MageMe\WebForms\Model\ResourceModel\Message as MessageResource;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package MageMe\WebForms\Model\ResourceModel\Message
 */
class Collection extends AbstractCollection
{
    /**
     * @var HtmlHelper
     */
    private $htmlHelper;

    public function __construct(
        HtmlHelper $htmlHelper,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->htmlHelper = $htmlHelper;
    }

    /**
     * Returns select count sql
     *
     * @return string
     */
    public function getSelectCountSql(): string
    {
        $select      = parent::getSelectCountSql();
        $countSelect = clone $this->getSelect();

        $countSelect->reset(Select::HAVING);

        return $select;
    }

    protected function _afterLoad()
    {
        foreach ($this as $item) {
            $item->setData(MessageInterface::MESSAGE, $this->htmlHelper->sanitizeHtml($item->getData(MessageInterface::MESSAGE)));
        }
        return parent::_afterLoad();
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Message::class, MessageResource::class);
    }

}
