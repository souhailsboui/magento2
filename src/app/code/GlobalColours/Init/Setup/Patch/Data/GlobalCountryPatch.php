<?php

namespace GlobalColours\Init\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

use Psr\Log\LoggerInterface;

class GlobalCountryPatch implements DataPatchInterface, PatchRevertableInterface
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
                'path' => 'general/country/destinations',
                'value' => 'AU,NZ,US,CA,GB,EU',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'general/country/allow',
                'value' => 'AU,NZ,US,CA,GB,EU',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'general/country/default',
                'value' => 'AU',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'general/locale/code',
                'value' => 'en_AU',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'general/locale/weight_unit',
                'value' => 'kgs',
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
                'path = ?' => 'general/country/default'
            ]
        );
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
                'path = ?' => 'general/country/allow'
            ]
        );
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
                'path = ?' => 'general/country/destinations'
            ]
        );
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
                'path = ?' => 'general/locale/code'
            ]
        );
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
                'path = ?' => 'general/locale/weight_unit'
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
