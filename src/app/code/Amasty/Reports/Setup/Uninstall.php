<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Setup;

use Amasty\Reports\Api\Data\NotificationInterface;
use Amasty\Reports\Api\Data\RuleInterface;
use Amasty\Reports\Model\ResourceModel\Abandoned\Cart;
use Amasty\Reports\Model\ResourceModel\Customers\Customers\Statistic;
use Amasty\Reports\Model\ResourceModel\RuleIndex;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this
            ->uninstallTables($setup)
            ->uninstallConfigData($setup);
    }

    private function uninstallTables(SchemaSetupInterface $setup): self
    {
        $tablesToDrop = [
            Cart::MAIN_TABLE,
            Statistic::REPORTS_CUSTOMERS_DAILY,
            Statistic::REPORTS_CUSTOMERS_YEARLY,
            Statistic::REPORTS_CUSTOMERS_WEEKLY,
            Statistic::REPORTS_CUSTOMERS_MONTHLY,
            NotificationInterface::TABLE_NAME,
            RuleInterface::TABLE_NAME,
            RuleIndex::MAIN_TABLE
        ];

        foreach ($tablesToDrop as $table) {
            $setup->getConnection()->dropTable(
                $setup->getTable($table)
            );
        }

        return $this;
    }

    private function uninstallConfigData(SchemaSetupInterface $setup): self
    {
        $configTable = $setup->getTable('core_config_data');
        $setup->getConnection()->delete($configTable, "`path` LIKE 'amasty_reports%'");

        return $this;
    }
}
