<?php

namespace MageMe\WebForms\Helper\Statistics;

use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Setup\Table\MessageTable;
use MageMe\WebForms\Setup\Table\ResultTable;

class ResultStat extends AbstractStat
{
    const ENTITY_TYPE = 'result';
    const TABLE_ALIAS = 'res';
    const KEY = self::TABLE_ALIAS . "." . ResultInterface::ID;

    const IS_UNREAD_REPLY = 'is_unread_reply';

    /** Codes */
    const CUSTOMER_MESSAGE_UNREAD = 'customer_message_unread';

    /**
     * @param int $resultId
     * @return bool
     * @noinspection SqlNoDataSourceInspection
     */
    public function isLastMessageFromCustomerUnread(int $resultId): bool
    {
        $connection = $this->getConnection();
        $sql        = sprintf("SELECT (%s)", $this->getSqlSelectUnreadReply($resultId));
        return (bool)$connection->fetchOne($sql);
    }

    /**
     * @param string $resultKey
     * @return string
     */
    public function getSqlSelectUnreadReply(string $resultKey = self::KEY): string
    {
        return sprintf("(%s) > 0", $this->getSqlSelectUnreadMessagesCountResult($resultKey));
    }

    #region Messages

    /**
     * @param string $selector
     * @param string $operator
     * @return void
     * @noinspection SqlNoDataSourceInspection
     */
    public function calculateResultUnreadMessagesCount(string $selector = self::KEY, string $operator = '='): void
    {
        $msgSql = $this->getSqlSelectUnreadMessagesCountResult($selector, $operator);
        $this->calcStat(
            self::KEY,
            self::ENTITY_TYPE,
            self::CUSTOMER_MESSAGE_UNREAD,
            $msgSql,
            $this->resourceConnection->getTableName(ResultTable::TABLE_NAME),
            self::TABLE_ALIAS);
    }

    /**
     * @param string $resultKey
     * @param string $operator
     * @return string
     * @noinspection SqlNoDataSourceInspection
     */
    public function getSqlSelectUnreadMessagesCountResult(string $resultKey = self::KEY, string $operator = '='): string
    {
        $messageTable = $this->resourceConnection->getTableName(MessageTable::TABLE_NAME);
        $prefix       = "$messageTable.";
        return sprintf("SELECT COUNT(%s) FROM %s WHERE %s %s %s AND %s = 1 AND %s = 0",
            MessageInterface::ID,
            $messageTable,
            $prefix . MessageInterface::RESULT_ID,
            $operator,
            $resultKey,
            $prefix . MessageInterface::IS_FROM_CUSTOMER,
            $prefix . MessageInterface::IS_READ
        );
    }
    #endregion

    /**
     * @param int $id
     * @param string $code
     * @param int $count
     * @return void
     */
    public function incStatValue(int $id, string $code, int $count = 1): void
    {
        $this->changeStatValue($count, '+', $id, self::ENTITY_TYPE, $code);
    }

    /**
     * @param int $id
     * @param string $code
     * @param int $count
     * @return void
     */
    public function decStatValue(int $id, string $code, int $count = 1): void
    {
        $this->changeStatValue($count, '-', $id, self::ENTITY_TYPE, $code);
    }
}