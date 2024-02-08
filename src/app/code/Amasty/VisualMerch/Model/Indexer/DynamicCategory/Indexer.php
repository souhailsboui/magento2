<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Indexer\DynamicCategory;

use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Action\ExecuteFull;
use Amasty\VisualMerch\Model\Indexer\DynamicCategory\Action\ExecutePartial;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewInterface;

class Indexer implements ActionInterface, MviewInterface
{
    public const CATEGORY_INDEXER_TYPE = 'category';
    public const PRODUCT_INDEXER_TYPE = 'product';

    /**
     * @var ExecuteFull
     */
    private $executeFull;

    /**
     * @var ExecutePartial
     */
    private $executePartial;

    public function __construct(
        ExecuteFull $executeFull,
        ExecutePartial $executePartial
    ) {
        $this->executeFull = $executeFull;
        $this->executePartial = $executePartial;
    }

    public function executeFull()
    {
        $this->executeFull->execute();
    }

    /**
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->executePartial->execute($ids);
    }

    /**
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->executePartial->execute([$id]);
    }

    /**
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->executePartial->execute($ids);
    }
}
