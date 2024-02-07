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

namespace MageMe\WebFormsCustomerRegistration\Setup;

use MageMe\WebForms\Setup\Table\FormTable;
use MageMe\WebFormsCustomerRegistration\Api\Data\FormInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 *
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer  = $setup;
        $connection = $setup->getConnection();
        $formTable  = $setup->getTable(FormTable::TABLE_NAME);

        $installer->startSetup();

        $connection->addColumn(
            $formTable,
            FormInterface::CR_IS_REGISTERED_ON_SUBMISSION,
            [
                Table::OPTION_TYPE => Table::TYPE_BOOLEAN,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
                'comment' => 'Register Customer on Form Submission'
            ]
        );

        $connection->addColumn(
            $formTable,
            FormInterface::CR_IS_CUSTOMER_EMAIL_UNIQUE,
            [
                Table::OPTION_TYPE => Table::TYPE_BOOLEAN,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
                'comment' => 'Unique Customer Email Address'
            ]
        );

        $connection->addColumn(
            $formTable,
            FormInterface::CR_DEFAULT_GROUP_ID,
            [
                Table::OPTION_TYPE => Table::TYPE_INTEGER,
                Table::OPTION_LENGTH => 11,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => true,
                'comment' => 'Default Customer Group'
            ]
        );

        $connection->addColumn(
            $formTable,
            FormInterface::CR_IS_REGISTERED_ON_APPROVAL,
            [
                Table::OPTION_TYPE => Table::TYPE_BOOLEAN,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
                'comment' => 'Register Customer on Result Approval'
            ]
        );

        $connection->addColumn(
            $formTable,
            FormInterface::CR_APPROVAL_GROUP_ID,
            [
                Table::OPTION_TYPE => Table::TYPE_INTEGER,
                Table::OPTION_LENGTH => 11,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => true,
                'comment' => 'Approval Customer Group'
            ]
        );

        $connection->addColumn(
            $formTable,
            FormInterface::CR_IS_DEFAULT_NOTIFICATION_ENABLED,
            [
                Table::OPTION_TYPE => Table::TYPE_BOOLEAN,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
                'comment' => 'Send Customer Default Magento Notification'
            ]
        );

        $connection->addColumn(
            $formTable,
            FormInterface::CR_MAP_SERIALIZED,
            [
                Table::OPTION_TYPE => Table::TYPE_TEXT,
                Table::OPTION_NULLABLE => true,
                'comment' => 'Attribute mapping in JSON'
            ]);

        $installer->endSetup();
    }
}
