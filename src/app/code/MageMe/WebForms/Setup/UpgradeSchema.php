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

namespace MageMe\WebForms\Setup;

use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Setup\Table\FieldsetTable;
use MageMe\WebForms\Setup\Table\FormTable;
use MageMe\WebForms\Setup\Table\MessageTable;
use MageMe\WebForms\Setup\Table\ResultTable;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use MageMe\WebForms\Setup\Table\StatisticsTable;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var StatisticsTable
     */
    private $statisticsTable;

    /**
     * @param StatisticsTable $statisticsTable
     */
    public function __construct(StatisticsTable $statisticsTable)
    {
        $this->statisticsTable = $statisticsTable;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $formTable     = $setup->getTable(FormTable::TABLE_NAME);
        $fieldsetTable = $setup->getTable(FieldsetTable::TABLE_NAME);
        $messageTable  = $setup->getTable(MessageTable::TABLE_NAME);

        if (version_compare($context->getVersion(), '3.0.12', '<')) {
            $setup->getConnection()->addColumn($formTable, FormInterface::IS_SUCCESS_SESSION_DISPLAYED,
                [
                    Table::OPTION_TYPE     => Table::TYPE_BOOLEAN,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT  => 0,
                    'comment'              => 'Add Success session message on redirection'
                ]);
        }

        if (version_compare($context->getVersion(), '3.0.16', '<')) {
            $setup->getConnection()->addColumn($fieldsetTable, FieldsetInterface::IS_LABEL_HIDDEN,
                [
                    Table::OPTION_TYPE     => Table::TYPE_BOOLEAN,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT  => 0,
                    'comment'              => 'Hide Label'
                ]);
        }

        if (version_compare($context->getVersion(), '3.0.17', '<')) {
            $setup->getConnection()->addColumn($formTable, FormInterface::ADMIN_NOTIFICATION_SENDER_NAME,
                [
                    Table::OPTION_TYPE   => Table::TYPE_TEXT,
                    Table::OPTION_LENGTH => 255,
                    'comment'            => 'Admin Notification Sender Name'
                ]);
            $setup->getConnection()->addColumn($formTable, FormInterface::ADMIN_NOTIFICATION_SENDER_EMAIL,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    'comment'          => 'Admin Notification Sender Email'
                ]);
        }

        if (version_compare($context->getVersion(), '3.0.18', '<')) {
            $setup->getConnection()->addColumn($formTable, 'unread_count',
                [
                    Table::OPTION_TYPE    => Table::TYPE_INTEGER,
                    Table::OPTION_LENGTH  => 11,
                    Table::OPTION_DEFAULT => 0,
                    'comment'             => 'Unread Count'
                ]);
        }

        if (version_compare($context->getVersion(), '3.1.0', '<')) {
            $oldPaths   = [
                'webforms/captcha/public_key',
                'webforms/captcha/private_key',
                'webforms/captcha/public_key3',
                'webforms/captcha/private_key3',
                'webforms/captcha/recaptcha_version',
                'webforms/captcha/position',
                'webforms/captcha/theme',
                'webforms/captcha/score_threshold',
                'webforms/captcha/validation_failure_message',
                'webforms/captcha/technical_failure_message',
            ];
            $connection = $setup->getConnection();
            $table      = 'core_config_data';
            foreach ($oldPaths as $oldPath) {
                $newPath = str_replace('webforms/captcha', 'webforms/captcha/recaptcha', $oldPath);
                $select  = $connection->select()->from(
                    $table
                )->where(
                    'path = ?',
                    $oldPath
                );
                $rows    = $connection->fetchAll($select);
                foreach ($rows as $oldData) {
                    $connection->insert($table, [
                        'scope'    => $oldData['scope'],
                        'scope_id' => $oldData['scope_id'],
                        'path'     => $newPath,
                        'value'    => $oldData['value']
                    ]);
                }
            }
        }

        if (version_compare($context->getVersion(), '3.2.0', '<')) {
            $setup->getConnection()->addColumn($messageTable, MessageInterface::IS_FROM_CUSTOMER,
                [
                    Table::OPTION_TYPE     => Table::TYPE_BOOLEAN,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT  => 0,
                    'comment'              => 'Is from customer'
                ]);
            $setup->getConnection()->addColumn($messageTable, MessageInterface::IS_READ,
                [
                    Table::OPTION_TYPE     => Table::TYPE_BOOLEAN,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT  => 0,
                    'comment'              => 'Is read'
                ]);
            $setup->getConnection()->addColumn($formTable, FormInterface::ADMIN_EMAIL_REPLY_TEMPLATE_ID,
                [
                    Table::OPTION_TYPE     => Table::TYPE_INTEGER,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => true,
                    Table::OPTION_DEFAULT  => 0,
                    'comment'              => 'Admin reply template'
                ]);
            $setup->getConnection()->dropColumn($formTable, 'unread_count');
            $this->statisticsTable->createTable($setup);
        }

        $setup->endSetup();
    }
}
