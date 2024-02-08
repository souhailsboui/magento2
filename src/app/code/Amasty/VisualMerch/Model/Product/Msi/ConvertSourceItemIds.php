<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Product\Msi;

use Amasty\VisualMerch\Model\ResourceModel\Product\Msi\LoadProductIdsByItemIds;
use Exception;
use Psr\Log\LoggerInterface;

class ConvertSourceItemIds
{
    /**
     * @var LoadProductIdsByItemIds
     */
    private $loadProductIdsByItemIds;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoadProductIdsByItemIds $loadProductIdsByItemIds, LoggerInterface $logger)
    {
        $this->loadProductIdsByItemIds = $loadProductIdsByItemIds;
        $this->logger = $logger;
    }

    public function execute(array $sourceItemIds): array
    {
        try {
            $productIds = $this->loadProductIdsByItemIds->execute($sourceItemIds);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $productIds = [];
        }

        return array_unique($productIds);
    }
}
