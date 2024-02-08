<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\Utilities;

use Magento\Framework\Stdlib\DateTime\DateTime;

class GetLocalToDate
{
    /**
     * @var GetDefaultToDate
     */
    private $getDefaultToDate;

    /**
     * @var GetLocalDate
     */
    private $getLocalDate;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        GetDefaultToDate $getDefaultToDate,
        GetLocalDate $getLocalDate,
        DateTime $dateTime
    ) {
        $this->getDefaultToDate = $getDefaultToDate;
        $this->getLocalDate = $getLocalDate;
        $this->dateTime = $dateTime;
    }

    public function execute(?string $defaultTo = null): string
    {
        $to = $defaultTo ?: $this->dateTime->gmtDate('Y-m-d', $this->getDefaultToDate->execute());

        return $this->getLocalDate->execute($to, 23, 59, 59);
    }
}
