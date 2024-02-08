<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver;

use Amasty\VisualMerch\Model\DynamicCategory\GetMatchedProductIds;
use Amasty\VisualMerch\Model\RuleFactory;

class ResolveIds
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var GetMatchedProductIds
     */
    private $getMatchedProductIds;

    public function __construct(
        RuleFactory $ruleFactory,
        GetMatchedProductIds $getMatchedProductIds
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->getMatchedProductIds = $getMatchedProductIds;
    }

    public function execute(int $storeId, string $conditionsSerialized): array
    {
        $amastyRule = $this->ruleFactory->create();
        $amastyRule->setConditionsSerialized($conditionsSerialized);

        return $this->getMatchedProductIds->execute($amastyRule->getConditions(), $storeId);
    }
}
