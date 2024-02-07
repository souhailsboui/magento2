<?php

namespace MageMe\WebForms\Helper\Statistics;

use MageMe\WebForms\Api\Data\StatisticsInterface;
use MageMe\WebForms\Setup\Table\StatisticsTable;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

abstract class AbstractStat
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return AdapterInterface
     */
    protected function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @param string $entityKey
     * @param string $entityType
     * @param string $code
     * @param string $valueSql
     * @param string $entityTable
     * @param string $entityTableAlias
     * @return void
     */
    protected function calcStat(
        string $entityKey,
        string $entityType,
        string $code,
        string $valueSql,
        string $entityTable,
        string $entityTableAlias
    ): void {
        $connection = $this->getConnection();
        $statTable  = $this->resourceConnection->getTableName(StatisticsTable::TABLE_NAME);
        $sql        = sprintf("INSERT INTO %s (`%s`, `%s`, `%s`, `%s`) " .
            "(SELECT %s, '%s', '%s', (%s) FROM %s as %s) " .
            "ON DUPLICATE KEY UPDATE `%s` = (%s)",
            // INSERT
            $statTable,
            StatisticsInterface::ENTITY_ID,
            StatisticsInterface::ENTITY_TYPE,
            StatisticsInterface::CODE,
            StatisticsInterface::VALUE,
            // ENTITY SELECT
            $entityKey,
            $entityType,
            $code,
            $valueSql,
            $entityTable,
            $entityTableAlias,
            // DUPLICATE KEY UPDATE
            StatisticsInterface::VALUE,
            str_replace($entityKey, "$statTable." . StatisticsInterface::ENTITY_ID, $valueSql)
        );
        $connection->query($sql);
    }

    /**
     * @param string $entityType
     * @param string $entityKey
     * @return string
     * @noinspection SqlDialectInspection
     */
    public function getJsonStatSql(string $entityType, string $entityKey): string
    {
        return sprintf("SELECT CONCAT(
                               '{',
                               GROUP_CONCAT(
                                   CONCAT(
                                       '\"',%s,'\":\"', %s, '\"'
                                   )
                               ),
                               '}'
                            ) FROM %s WHERE %s='%s' AND %s=%s",
            StatisticsInterface::CODE,
            StatisticsInterface::VALUE,
            $this->resourceConnection->getTableName(StatisticsTable::TABLE_NAME),
            StatisticsInterface::ENTITY_TYPE,
            $entityType,
            StatisticsInterface::ENTITY_ID,
            $entityKey
        );
    }

    /**
     * @param int $count
     * @param string $operator
     * @param int $entityId
     * @param string $entityType
     * @param string $code
     * @return void
     */
    protected function changeStatValue(
        int    $count,
        string $operator,
        int    $entityId,
        string $entityType,
        string $code
    ): void {
        $connection = $this->getConnection();
        $statTable  = $this->resourceConnection->getTableName(StatisticsTable::TABLE_NAME);
        $sql        = sprintf("INSERT INTO %s (`%s`, `%s`, `%s`, `%s`) 
                                      VALUES (%s, '%s', '%s', '%s')
                                      ON DUPLICATE KEY UPDATE `%s` = `%s` %s %s",
            $statTable,
            StatisticsInterface::ENTITY_ID,
            StatisticsInterface::ENTITY_TYPE,
            StatisticsInterface::CODE,
            StatisticsInterface::VALUE,
            $entityId,
            $entityType,
            $code,
            $count,
            StatisticsInterface::VALUE,
            StatisticsInterface::VALUE,
            $operator,
            $count
        );
        $connection->query($sql);
    }

    /**
     * @param int $id
     * @param string $code
     * @param int $count
     * @return void
     */
    abstract public function incStatValue(int $id, string $code, int $count = 1): void;

    /**
     * @param int $id
     * @param string $code
     * @param int $count
     * @return void
     */
    abstract public function decStatValue(int $id, string $code, int $count = 1): void;
}