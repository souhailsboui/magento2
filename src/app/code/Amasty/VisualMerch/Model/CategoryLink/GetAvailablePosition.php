<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\CategoryLink;

use Amasty\VisualMerch\Model\ResourceModel\CategoryLink\GetMaxPosition;

class GetAvailablePosition
{
    /**
     * @var GetMaxPosition
     */
    private $getMaxPosition;

    public function __construct(GetMaxPosition $getMaxPosition)
    {
        $this->getMaxPosition = $getMaxPosition;
    }

    public function execute(int $categoryId): int
    {
        $maxPosition = $this->getMaxPosition->execute($categoryId);
        return ++$maxPosition;
    }
}
