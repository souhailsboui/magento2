<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\DynamicCategory\Temporary;

use Amasty\VisualMerch\Model\ResourceModel\DynamicCategory\CategoryTemporary;
use Magento\Framework\Stdlib\DateTime as DateTimeFormatter;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DeleteExpired
{
    private const DEFAULT_LIFETIME = 86400;

    /**
     * @var DateTimeFormatter
     */
    private $dateTimeFormatter;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CategoryTemporary
     */
    private $categoryTemporary;

    public function __construct(
        DateTimeFormatter $dateTimeFormatter,
        DateTime $dateTime,
        CategoryTemporary $categoryTemporary
    ) {
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->dateTime = $dateTime;
        $this->categoryTemporary = $categoryTemporary;
    }

    /**
     * @param int|null $lifetime Period for clear in seconds.
     */
    public function execute(?int $lifetime = null): void
    {
        $lifetime = $lifetime ?? self::DEFAULT_LIFETIME;
        $this->categoryTemporary->deleteOldData(
            $this->dateTimeFormatter->formatDate($this->dateTime->gmtTimestamp() - $lifetime)
        );
    }
}
