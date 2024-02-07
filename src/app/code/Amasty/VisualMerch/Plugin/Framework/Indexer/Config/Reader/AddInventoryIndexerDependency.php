<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Framework\Indexer\Config\Reader;

use Magento\Framework\Indexer\Config\Reader;

class AddInventoryIndexerDependency
{
    private const INVENTORY_INDEXER_CODE = 'inventory';
    /**
     * @var string[]
     */
    private $indexersDependsOfInventory;

    public function __construct(array $indexersDependsOfInventory = [])
    {
        $this->indexersDependsOfInventory = $indexersDependsOfInventory;
    }

    /**
     * @see Reader::read
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(Reader $reader, array $config): array
    {
        foreach ($this->indexersDependsOfInventory as $indexerName) {
            if (isset($config[$indexerName]['dependencies'])) {
                $config[$indexerName]['dependencies'][] = self::INVENTORY_INDEXER_CODE;
            }
        }
        return $config;
    }
}
