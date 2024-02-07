<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser Core by Amasty for Magento 2 (System)
 */

namespace Amasty\VisualMerchCore\Setup\Patch\DeclarativeSchemaApplyBefore;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * It is necessary to drop the tables because the column type "value" cannot be changed if there is an index,
 * this cannot be done through a declarative schema, as the type of the column changes
 * the first and fatal for all time. Also, the storage structure changes and an error falls on non-unique values
 * for old data. After deleting the table, re-create through the schema.
 */
class DropTables implements SchemaPatchInterface
{
    private const TABLES_TO_DROP = [
        'amasty_merchandiser_product_index_eav',
        'amasty_merchandiser_product_index_eav_replica',
        'amasty_merchandiser_product_index_eav_tmp'
    ];

    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $connection = $this->schemaSetup->getConnection();

        foreach (self::TABLES_TO_DROP as $tableName) {
            $connection->dropTable($this->schemaSetup->getTable($tableName));
        }

        return $this;
    }
}
