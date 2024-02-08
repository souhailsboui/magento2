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

namespace MageMe\WebFormsZoho\Setup;

use MageMe\WebForms\Setup\Table\FormTable;
use MageMe\WebFormsZoho\Api\Data\FormInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $table = $setup->getTable(FormTable::TABLE_NAME);

        /** Zoho CRM */
        $setup->getConnection()->addColumn($table, 'is_zoho_enabled',
            [
                Table::OPTION_TYPE => Table::TYPE_BOOLEAN,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
                'comment' => 'Zoho integration enabled'
            ]);

        $setup->getConnection()->addColumn($table, 'zoho_email_field_id',
            [
                Table::OPTION_TYPE => Table::TYPE_INTEGER,
                Table::OPTION_LENGTH => 11,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => true,
                'comment' => 'Zoho Email Override'
            ]);

        $setup->getConnection()->addColumn($table, 'zoho_lead_owner',
            [
                Table::OPTION_TYPE => Table::TYPE_TEXT,
                Table::OPTION_NULLABLE => true,
                'comment' => 'Zoho Lead Owner'
            ]);

        $setup->getConnection()->addColumn($table, 'zoho_lead_source',
            [
                Table::OPTION_TYPE => Table::TYPE_TEXT,
                Table::OPTION_NULLABLE => true,
                'comment' => 'Zoho Lead Owner'
            ]);

        $setup->getConnection()->addColumn($table, 'zoho_map_fields_serialized',
            [
                Table::OPTION_TYPE => Table::TYPE_TEXT,
                Table::OPTION_NULLABLE => true,
                'comment' => 'Zoho Map Fields in JSON'
            ]);

        $setup->endSetup();
    }
}
