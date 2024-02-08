<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\Utilities;

use Magento\Framework\Stdlib\DateTime\DateTime;

class GetLocalFromDate
{
    /**
     * @var GetDefaultFromDate
     */
    private $getDefaultFromDate;

    /**
     * @var GetLocalDate
     */
    private $getLocalDate;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        GetDefaultFromDate $getDefaultFromDate,
        GetLocalDate $getLocalDate,
        DateTime $dateTime
    ) {
        $this->getDefaultFromDate = $getDefaultFromDate;
        $this->getLocalDate = $getLocalDate;
        $this->dateTime = $dateTime;
    }

    public function execute(?string $defaultFrom = null): string
    {
        $from = $defaultFrom ?: $this->dateTime->gmtDate('Y-m-d', $this->getDefaultFromDate->execute());

        return $this->getLocalDate->execute($from);
    }
}
