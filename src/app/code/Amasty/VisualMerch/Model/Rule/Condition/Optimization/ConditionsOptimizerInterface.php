<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition\Optimization;

use Magento\Rule\Model\Condition\Combine as MagentoCombineConditions;

interface ConditionsOptimizerInterface
{
    public function optimize(MagentoCombineConditions $conditions): void;
}
