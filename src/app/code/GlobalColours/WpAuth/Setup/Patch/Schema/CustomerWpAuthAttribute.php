<?php

namespace GlobalColours\WpAuth\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CustomerWpAuthAttribute implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * CustomerIsWpAuthAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }


    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }


    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('customer_entity'),
            'is_wp_auth',
            [
                'type'     => Table::TYPE_BOOLEAN,
                'nullable' => true,
                'comment'  => "Is Wp Auth",
                'default' => 0
            ]
        );
        $this->moduleDataSetup->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->startSetup();
        $this->moduleDataSetup->getConnection()->dropColumn(
            $this->moduleDataSetup->getTable('customer_entity'),
            'is_wp_auth'
        );
        $this->moduleDataSetup->endSetup();
    }
}
