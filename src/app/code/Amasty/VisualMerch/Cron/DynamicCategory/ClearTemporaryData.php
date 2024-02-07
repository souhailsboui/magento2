<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Cron\DynamicCategory;

use Amasty\VisualMerch\Model\DynamicCategory\Temporary\DeleteExpired;

class ClearTemporaryData
{
    /**
     * @var DeleteExpired
     */
    private $deleteExpired;

    public function __construct(DeleteExpired $deleteExpired)
    {
        $this->deleteExpired = $deleteExpired;
    }

    public function execute(): void
    {
        $this->deleteExpired->execute();
    }
}
