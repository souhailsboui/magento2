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

namespace MageMe\WebForms\Model\ResourceModel;

use MageMe\WebForms\Api\Data\FileMessageInterface;
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Helper\HtmlHelper;
use MageMe\WebForms\Helper\StatisticsHelper;
use MageMe\WebForms\Setup\Table\MessageTable;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Message resource model
 *
 */
class Message extends AbstractDb
{
    const DB_TABLE = MessageTable::TABLE_NAME;
    const ID_FIELD = MessageInterface::ID;

    /**
     * @inheritdoc
     */
    protected $nullableFK = [
        MessageInterface::USER_ID
    ];

    /**
     * @var FileMessageRepositoryInterface
     */
    protected $fileMessageRepository;
    /**
     * @var StatisticsHelper
     */
    protected $statisticsHelper;

    /**
     * @var HtmlHelper
     */
    private $htmlHelper;

    /**
     * Message constructor.
     *
     * @param StatisticsHelper $statisticsHelper
     * @param HtmlHelper $htmlHelper
     * @param FileMessageRepositoryInterface $fileMessageRepository
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        StatisticsHelper               $statisticsHelper,
        HtmlHelper                     $htmlHelper,
        FileMessageRepositoryInterface $fileMessageRepository,
        Context                        $context,
        ?string                        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->fileMessageRepository = $fileMessageRepository;
        $this->statisticsHelper      = $statisticsHelper;
        $this->htmlHelper            = $htmlHelper;
    }

    /**
     * @param AbstractModel|MessageInterface $object
     * @return Message
     * @throws CouldNotDeleteException
     */
    protected function _beforeDelete(AbstractModel $object): Message
    {
        //delete files
        /** @var FileMessageInterface $files */
        $files = $this->fileMessageRepository->getListByMessageId($object->getId())->getItems();
        foreach ($files as $file) {
            $this->fileMessageRepository->delete($file);
        }
        return parent::_beforeDelete($object);
    }

    /**
     * @param AbstractModel|MessageInterface $object
     * @return $this
     */
    protected function _afterDelete(AbstractModel $object): Message
    {
        parent::_afterDelete($object);
        $this->statisticsHelper->processMessageAfterDelete($object);
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return AbstractDb|Message
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $object->setData(MessageInterface::MESSAGE, $this->htmlHelper->sanitizeHtml($object->getData(MessageInterface::MESSAGE)));
        return parent::_afterLoad($object);
    }

    /**
     * @param AbstractModel $object
     * @return AbstractDb|Message
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->setData(MessageInterface::MESSAGE, $this->htmlHelper->sanitizeHtml($object->getData(MessageInterface::MESSAGE)));
        return parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel|MessageInterface $object
     * @return Message
     */
    protected function _afterSave(AbstractModel $object): Message
    {
        if ($object->isObjectNew()) {
            $this->statisticsHelper->processNewMessageAfterSave($object);
        } else {
            $this->statisticsHelper->processMessageAfterSave($object);
        }
        return parent::_afterSave($object);
    }
}
