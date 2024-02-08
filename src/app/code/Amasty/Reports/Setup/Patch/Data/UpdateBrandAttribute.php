<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateBrandAttribute implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->moduleManager = $moduleManager;
    }

    public function apply(): self
    {
        if ($this->moduleManager->isEnabled('Amasty_ShopbyBrand')) {
            $connection = $this->moduleDataSetup->getConnection();
            $tableName = $this->moduleDataSetup->getTable('core_config_data');

            $select = $this->moduleDataSetup->getConnection()->select()
                ->from($tableName, ['scope', 'scope_id', 'path', 'value'])
                ->where('path = \'amshopby_brand/general/attribute_code\'');

            $settings = $connection->fetchAll($select);

            foreach ($settings as $config) {
                if ($config['value']) {
                    $connection->insertOnDuplicate(
                        $tableName,
                        [
                            'scope_id' => $config['scope_id'],
                            'scope' => $config['scope'],
                            'value' => $config['value'],
                            'path' => 'amasty_reports/general/report_brand'
                        ]
                    );
                }
            }
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
