<?php

namespace MageMe\WebForms\Helper\Statistics;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\StatisticsInterface;
use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use MageMe\WebForms\Model\ResourceModel\Form as ResourceForm;
use MageMe\WebForms\Setup\Table\FormTable;
use MageMe\WebForms\Setup\Table\ResultTable;
use MageMe\WebForms\Setup\Table\StatisticsTable;

class FormStat extends AbstractStat
{
    const ENTITY_TYPE = ResourceForm::ENTITY_TYPE;
    const TABLE_ALIAS = 'form';
    const KEY = self::TABLE_ALIAS . "." . FormInterface::ID;

    /** Prefix */
    const RESULT_STATUS_PREFIX = 'status_';
    const RESULT_CUSTOM_STATUS_PREFIX = 'custom_status_';

    /** Codes */
    const RESULT_ALL = 'result_all';
    const RESULT_UNREAD = 'result_unread';
    const RESULT_REPLIED = 'result_replied';
    const RESULT_MESSAGE_UNREAD = 'result_message_unread';
    const RESULT_FOLLOW_UP = 'result_follow_up';
    const RESULT_STATUS_NOT_APPROVED = self::RESULT_STATUS_PREFIX . 'notapproved';
    const RESULT_STATUS_PENDING = self::RESULT_STATUS_PREFIX . 'pending';
    const RESULT_STATUS_APPROVED = self::RESULT_STATUS_PREFIX . 'approved';
    const RESULT_STATUS_COMPLETED = self::RESULT_STATUS_PREFIX . 'completed';

    /**
     * @var array|string[]
     */
    private $resultStatuses = [
        ApprovalStatus::STATUS_NOT_APPROVED => self::RESULT_STATUS_NOT_APPROVED,
        ApprovalStatus::STATUS_PENDING => self::RESULT_STATUS_PENDING,
        ApprovalStatus::STATUS_APPROVED => self::RESULT_STATUS_APPROVED,
        ApprovalStatus::STATUS_COMPLETED => self::RESULT_STATUS_COMPLETED
    ];


    /**
     * @return int
     * @noinspection SqlDialectInspection
     */
    public function getTotalUnreadCount(): int
    {
        $connection = $this->getConnection();
        $sql        = sprintf("SELECT SUM(`%s`) FROM %s WHERE %s = '%s' AND `%s` = '%s'",
            StatisticsInterface::VALUE,
            $this->resourceConnection->getTableName(StatisticsTable::TABLE_NAME),
            StatisticsInterface::ENTITY_TYPE,
            self::ENTITY_TYPE,
            StatisticsInterface::CODE,
            self::RESULT_UNREAD
        );
        return (int)$connection->fetchOne($sql);
    }

    #region All

    /**
     * @param string $selector
     * @param string $operator
     * @return void
     */
    public function calculateFormAllResultCount(string $selector = self::KEY, string $operator = '='): void
    {
        $this->calcStat(
            self::KEY,
            self::ENTITY_TYPE,
            self::RESULT_ALL,
            $this->getSqlSelectAllResultCountForm($selector, $operator),
            $this->resourceConnection->getTableName(FormTable::TABLE_NAME),
            self::TABLE_ALIAS
        );
    }

    /**
     * @param string $formKey
     * @param string $operator
     * @return string
     */
    protected function getSqlSelectAllResultCountForm(string $formKey = self::KEY, string $operator = '='): string
    {
        return sprintf("SELECT COUNT(%s) FROM %s as res WHERE res.%s %s %s",
            ResultInterface::ID,
            $this->resourceConnection->getTableName(ResultTable::TABLE_NAME),
            ResultInterface::FORM_ID,
            $operator,
            $formKey
        );
    }
    #endregion

    #region Unread
    /**
     * @param string $selector
     * @param string $operator
     * @return void
     */
    public function calculateFormUnreadResultCount(string $selector = self::KEY, string $operator = '='): void
    {
        $this->calcStat(
            self::KEY,
            self::ENTITY_TYPE,
            self::RESULT_UNREAD,
            $this->getSqlSelectUnreadResultCountForm($selector, $operator),
            $this->resourceConnection->getTableName(FormTable::TABLE_NAME),
            self::TABLE_ALIAS
        );
    }

    /**
     * @param string $formKey
     * @param string $operator
     * @return string
     */
    protected function getSqlSelectUnreadResultCountForm(string $formKey = self::KEY, string $operator = '='): string
    {
        return sprintf("SELECT COUNT(%s) FROM %s as res WHERE res.%s %s %s AND res.%s = 0",
            ResultInterface::ID,
            $this->resourceConnection->getTableName(ResultTable::TABLE_NAME),
            ResultInterface::FORM_ID,
            $operator,
            $formKey,
            ResultInterface::IS_READ
        );
    }
    #endregion

    #region Replied
    /**
     * @param string $selector
     * @param string $operator
     * @return void
     */
    public function calculateFormRepliedResultCount(string $selector = self::KEY, string $operator = '='): void
    {
        $this->calcStat(
            self::KEY,
            self::ENTITY_TYPE,
            self::RESULT_REPLIED,
            $this->getSqlSelectRepliedResultCountForm($selector, $operator),
            $this->resourceConnection->getTableName(FormTable::TABLE_NAME),
            self::TABLE_ALIAS
        );
    }

    /**
     * @param string $formKey
     * @param string $operator
     * @return string
     */
    protected function getSqlSelectRepliedResultCountForm(string $formKey = self::KEY, string $operator = '='): string
    {
        return sprintf("SELECT COUNT(%s) FROM %s as res WHERE res.%s %s %s AND res.%s = 1",
            ResultInterface::ID,
            $this->resourceConnection->getTableName(ResultTable::TABLE_NAME),
            ResultInterface::FORM_ID,
            $operator,
            $formKey,
            ResultInterface::IS_REPLIED
        );
    }
    #endregion

    #region Messages
    /**
     * @param string $selector
     * @param string $operator
     * @return void
     */
    public function calculateFormUnreadMessagesCount(string $selector = self::KEY, string $operator = '='): void
    {
        $this->calcStat(
            self::KEY,
            self::ENTITY_TYPE,
            self::RESULT_MESSAGE_UNREAD,
            $this->getSqlSelectUnreadMessagesCountForm($selector, $operator),
            $this->resourceConnection->getTableName(FormTable::TABLE_NAME),
            self::TABLE_ALIAS
        );
    }

    /**
     * @param string $formKey
     * @param string $operator
     * @return string
     */
    protected function getSqlSelectUnreadMessagesCountForm(string $formKey = self::KEY, string $operator = '='): string
    {
        return sprintf("COALESCE((SELECT SUM(stat.%s) FROM %s as stat " .
            "JOIN %s as res ON res.%s = stat.%s " .
            "WHERE stat.%s = '%s' AND stat.%s = '%s' AND res.%s %s %s), 0)",
            StatisticsInterface::VALUE,
            $this->resourceConnection->getTableName(StatisticsTable::TABLE_NAME),
            $this->resourceConnection->getTableName(ResultTable::TABLE_NAME),
            ResultInterface::ID,
            StatisticsInterface::ENTITY_ID,
            StatisticsInterface::ENTITY_TYPE,
            ResultStat::ENTITY_TYPE,
            StatisticsInterface::CODE,
            ResultStat::CUSTOMER_MESSAGE_UNREAD,
            ResultInterface::FORM_ID,
            $operator,
            $formKey
        );
    }
    #endregion

    #region FollowUp
    /**
     * @param string $selector
     * @param string $operator
     * @return void
     */
    public function calculateFormFollowUpCount(string $selector = self::KEY, string $operator = '='): void
    {
        $this->calcStat(
            self::KEY,
            self::ENTITY_TYPE,
            self::RESULT_FOLLOW_UP,
            $this->getSqlSelectFollowUpCountForm($selector, $operator),
            $this->resourceConnection->getTableName(FormTable::TABLE_NAME),
            self::TABLE_ALIAS
        );
    }

    /**
     * @param string $formKey
     * @param string $operator
     * @return string
     */
    protected function getSqlSelectFollowUpCountForm(string $formKey = self::KEY, string $operator = '='): string
    {
        return sprintf("SELECT COUNT(stat.%s) FROM %s as stat " .
            "JOIN %s as res ON res.%s = stat.%s " .
            "WHERE stat.%s = '%s' AND stat.%s = '%s' AND res.%s %s %s AND stat.%s > 0",
            StatisticsInterface::ENTITY_ID,
            $this->resourceConnection->getTableName(StatisticsTable::TABLE_NAME),
            $this->resourceConnection->getTableName(ResultTable::TABLE_NAME),
            ResultInterface::ID,
            StatisticsInterface::ENTITY_ID,
            StatisticsInterface::ENTITY_TYPE,
            ResultStat::ENTITY_TYPE,
            StatisticsInterface::CODE,
            ResultStat::CUSTOMER_MESSAGE_UNREAD,
            ResultInterface::FORM_ID,
            $operator,
            $formKey,
            StatisticsInterface::VALUE
        );
    }
    #endregion

    #region Status
    /**
     * @param int $status
     * @return string
     */
    public function getStatusCode(int $status): string
    {
        if (!isset($this->resultStatuses[$status])) {
            return '';
        }
        return $this->resultStatuses[$status];
    }

    /**
     * @param int $status
     * @param string $selector
     * @param string $operator
     * @return void
     */
    public function calculateFormResultStatusCount(int    $status,
                                                   string $selector = self::KEY,
                                                   string $operator = '='
    ): void {
        $this->calcStat(
            self::KEY,
            self::ENTITY_TYPE,
            $this->getStatusCode($status),
            $this->getSqlSelectResultStatusCountForm($status, $selector, $operator),
            $this->resourceConnection->getTableName(FormTable::TABLE_NAME),
            self::TABLE_ALIAS
        );
    }

    /**
     * @param int $status
     * @param string $formKey
     * @param string $operator
     * @return string
     */
    protected function getSqlSelectResultStatusCountForm(
        int    $status,
        string $formKey = self::KEY,
        string $operator = '='
    ): string {
        return sprintf("SELECT COUNT(%s) FROM %s as res WHERE res.%s %s %s AND res.%s = %s",
            ResultInterface::ID,
            $this->resourceConnection->getTableName(ResultTable::TABLE_NAME),
            ResultInterface::FORM_ID,
            $operator,
            $formKey,
            ResultInterface::APPROVED,
            $status
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
