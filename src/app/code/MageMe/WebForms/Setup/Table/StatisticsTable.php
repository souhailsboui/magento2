<?php

namespace MageMe\WebForms\Setup\Table;

use MageMe\WebForms\Api\Data\StatisticsInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class StatisticsTable extends AbstractTable
{
    const TABLE_NAME = 'mm_webforms_statistics';

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getTableConfig(SchemaSetupInterface $setup): Table
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(StatisticsInterface::ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ], 'Id')
            ->addColumn(StatisticsInterface::ENTITY_ID, Table::TYPE_INTEGER, null, [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ], 'Entity ID')
            ->addColumn(StatisticsInterface::ENTITY_TYPE, Table::TYPE_TEXT, 10, [
                Table::OPTION_NULLABLE => false,
            ], 'Entity Type')
            ->addColumn(StatisticsInterface::CODE, Table::TYPE_TEXT, 255, [
                Table::OPTION_NULLABLE => false,
            ], 'Code')
            ->addColumn(StatisticsInterface::VALUE, Table::TYPE_TEXT, null, [
            ], 'Value')
            ->setComment(
                'WebForms Statistics'
            )
            ->addIndex(
                $setup->getIdxName(
                    self::TABLE_NAME,
                    [
                        StatisticsInterface::ENTITY_ID,
                        StatisticsInterface::ENTITY_TYPE,
                        StatisticsInterface::CODE
                    ],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [
                    StatisticsInterface::ENTITY_ID,
                    StatisticsInterface::ENTITY_TYPE,
                    StatisticsInterface::CODE
                ],
                [
                    'type' => AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            );
    }
}