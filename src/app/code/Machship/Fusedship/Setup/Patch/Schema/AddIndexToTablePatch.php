<?php
namespace Machship\Fusedship\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;

class AddIndexToTablePatch implements SchemaPatchInterface
{
    private $moduleDataSetup;
    private $resourceConnection;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resourceConnection
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConnection = $resourceConnection;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $tableName = $this->moduleDataSetup->getTable('fusedship_product_cartons');

        $connection  = $this->resourceConnection->getConnection();
        if (!$connection->isTableExists($tableName)) {
            $this->createProductCartonsTable($this->moduleDataSetup);
        }

        $indexName = 'product_id_index';
        $indexColumns = ['product_id'];

        $this->moduleDataSetup->getConnection()->addIndex($tableName, $indexName, $indexColumns);

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    private function createProductCartonsTable($installer)
    {
        // Fusedship Product Cartons
        $product_cartons_columns = [
            [
                'name' => 'carton_id',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'comments' => 'Carton ID'
            ],
            [
                'name' => 'product_id',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'comments' => 'Product ID'
            ],

            [
                'name' => 'carton_length',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'size' => 20,
                'options' => [],
                'comments' => 'Carton Length'
            ],

            [
                'name' => 'carton_width',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'size' => 20,
                'options' => [],
                'comments' => 'Carton Width'
            ],


            [
                'name' => 'carton_height',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'size' => 20,
                'options' => [],
                'comments' => 'Carton Height'
            ],

            [
                'name' => 'carton_weight',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'size' => 20,
                'options' => [],
                'comments' => 'Carton Weight'
            ],

            [
                'name' => 'package_type',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'size' => 20,
                'options' => [],
                'comments' => 'Package Type'
            ],

            [
                'name' => 'consolidated',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'size' => 20,
                'options' => [],
                'comments' => 'Can this be consolidated?'
            ],

        ];

        $cartonsTable = $installer->getConnection()
            ->newTable($installer->getTable('fusedship_product_cartons'));


        foreach($product_cartons_columns as $column) {
            $cartonsTable->addColumn($column['name'], $column['type'], $column['size'], $column['options'], $column['comments']);
        }

        $cartonsTable->setComment('Fusedship Product Cartons Table');

        $installer->getConnection()->createTable($cartonsTable);


        // Fusedship Product Data
        $products_data_columns = [
            [
                'name' => 'data_id',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'comments' => 'Product Data ID'
            ],
            [
                'name' => 'product_id',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'comments' => 'Product ID'
            ],

            [
                'name' => 'use_fusedship_rates',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'size' => null,
                'options' => [
                    'nullable' => true,
                    'default' => false,
                ],
                'comments' => 'Use fusedship rates for this product'
            ]

        ];


        $productsDataTable = $installer->getConnection()
            ->newTable($installer->getTable('fusedship_product_data'));

        foreach($products_data_columns as $column) {
            $productsDataTable->addColumn($column['name'], $column['type'], $column['size'], $column['options'], $column['comments']);
        }

        $productsDataTable->setComment('Fusedship Product Data Table');

        $installer->getConnection()->createTable($productsDataTable);

    }
}