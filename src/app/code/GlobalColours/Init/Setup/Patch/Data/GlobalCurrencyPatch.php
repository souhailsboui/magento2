<?php

namespace GlobalColours\Init\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

use Psr\Log\LoggerInterface;

class GlobalCurrencyPatch implements DataPatchInterface, PatchRevertableInterface
{
    private $moduleDataSetup;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->revert();

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'currency/options/default',
                'value' => 'AUD',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'currency/options/base',
                'value' => 'AUD',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'currency/options/allow',
                'value' => 'AUD,NZD,USD,CAD,GBP,EUR',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );


        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
                'path like ?' => 'currency/options/%'
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}
