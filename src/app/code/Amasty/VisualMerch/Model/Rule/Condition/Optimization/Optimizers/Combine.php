<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition\Optimization\Optimizers;

use Amasty\VisualMerch\Model\Rule\Condition\Optimization\ConditionsOptimizerInterface;
use Magento\Rule\Model\Condition\Combine as MagentoCombineConditions;

class Combine implements ConditionsOptimizerInterface
{
    /**
     * @var ConditionsOptimizerInterface[]
     */
    private $optimizers;

    /**
     * @param ConditionsOptimizerInterface[] $optimizers
     */
    public function __construct(
        array $optimizers = []
    ) {
        $this->optimizers = $optimizers;
    }

    public function optimize(MagentoCombineConditions $conditions): void
    {
        foreach ($this->optimizers as $optimizer) {
            if ($optimizer instanceof ConditionsOptimizerInterface) {
                $optimizer->optimize($conditions);
            }
        }
    }
}
