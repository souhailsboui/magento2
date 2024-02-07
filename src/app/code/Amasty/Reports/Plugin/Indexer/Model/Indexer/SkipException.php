<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Plugin\Indexer\Model\Indexer;

use Amasty\Reports\Model\Indexer\Rule\ProductProcessor;
use Amasty\Reports\Model\Indexer\Rule\RuleProcessor;
use Magento\Framework\Mview\ConfigInterface;
use Magento\Indexer\Model\Indexer;

/**
 * Fix an issue - after first setup:upgrade, mview.xml of the module is not collecting.
 */
class SkipException
{
    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function aroundIsScheduled(Indexer $subject, callable $proceed): bool
    {
        if (($subject->getIndexerId() === ProductProcessor::INDEXER_ID
            || $subject->getIndexerId() === RuleProcessor::INDEXER_ID)
            && !$this->config->getView($subject->getViewId())
        ) {
            return false;
        }

        return $proceed();
    }

    public function aroundReindexAll(Indexer $subject, callable $proceed): ?callable
    {
        if (($subject->getIndexerId() === ProductProcessor::INDEXER_ID
            || $subject->getIndexerId() === RuleProcessor::INDEXER_ID)
            && !$this->config->getView($subject->getViewId())
        ) {
            return null;
        }

        return $proceed();
    }
}
