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
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $table = $setup->getTable(FormTable::TABLE_NAME);

        if (version_compare($context->getVersion(), '3.0.1', '<')) {
            $setup->getConnection()->changeColumn($table,
                'is_zoho_enabled',
                FormInterface::ZOHO_CRM_IS_LEAD_ENABLED,
                [
                    Table::OPTION_TYPE => Table::TYPE_BOOLEAN,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT => 0,
                    'comment' => 'Zoho CRM Lead enabled'
                ]);

            $setup->getConnection()->changeColumn($table,
                'zoho_email_field_id',
                FormInterface::ZOHO_CRM_EMAIL_FIELD_ID,
                [
                    Table::OPTION_TYPE => Table::TYPE_INTEGER,
                    Table::OPTION_LENGTH => 11,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho CRM Email Override'
                ]);

            $setup->getConnection()->changeColumn($table,
                'zoho_lead_owner',
                FormInterface::ZOHO_CRM_LEAD_OWNER,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho CRM Lead Owner'
                ]);

            $setup->getConnection()->changeColumn($table,
                'zoho_lead_source',
                FormInterface::ZOHO_CRM_LEAD_SOURCE,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho CRM Lead Source'
                ]);

            $setup->getConnection()->changeColumn($table,
                'zoho_map_fields_serialized',
                FormInterface::ZOHO_CRM_MAP_FIELDS_SERIALIZED,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho CRM Map Fields in JSON'
                ]);

            /** Zoho Desk */
            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_IS_TICKET_ENABLED,
                [
                    Table::OPTION_TYPE => Table::TYPE_BOOLEAN,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT => 0,
                    'comment' => 'Zoho Desk Ticket enabled'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_EMAIL_FIELD_ID,
                [
                    Table::OPTION_TYPE => Table::TYPE_INTEGER,
                    Table::OPTION_LENGTH => 11,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Email Override'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_CONTACT_ID,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Contact'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_DEPARTMENT_ID,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Department'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_TICKET_STATUS,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Ticket Status'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_TICKET_OWNER,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Ticket Owner'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_TICKET_CHANNEL,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Ticket Channel'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_TICKET_CLASSIFICATION,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Ticket Classification'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_TICKET_PRIORITY,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Ticket Language'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_TICKET_LANGUAGE,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Ticket Language'
                ]);

            $setup->getConnection()->addColumn($table, FormInterface::ZOHO_DESK_MAP_FIELDS_SERIALIZED,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_NULLABLE => true,
                    'comment' => 'Zoho Desk Map Fields in JSON'
                ]);
        }

        $setup->endSetup();
    }
}