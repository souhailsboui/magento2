<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    private const CATALOG_CATEGORY_PRODUCT_STATIC_TABLE = 'catalog_category_product_static';

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $table = $setup->getTable(self::CATALOG_CATEGORY_PRODUCT_STATIC_TABLE);
        $setup->getConnection()->dropTable($table);
    }
}
