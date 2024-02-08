<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Filters;

use Amasty\Reports\Model\Utilities\GetDefaultFromDate;
use Amasty\Reports\Model\Utilities\GetLocalDate;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\DateTime;

class AddFromFilter
{
    /**
     * @var RequestFiltersProvider
     */
    private $filtersProvider;

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
        RequestFiltersProvider $filtersProvider,
        DateTime $dateTime
    ) {
        $this->filtersProvider = $filtersProvider;
        $this->getDefaultFromDate = $getDefaultFromDate;
        $this->getLocalDate = $getLocalDate;
        $this->dateTime = $dateTime;
    }

    /**
     * @param AbstractDb|Select  $object
     */
    public function execute(
        $object,
        string $dateFiled = 'created_at',
        string $tablePrefix = 'main_table',
        ?string $defaultFrom = null
    ): void {
        $filters = $this->filtersProvider->execute();
        if ($defaultFrom !== null) {
            $from = $defaultFrom;
        } else {
            $from = $filters['from'] ?? $this->dateTime->gmtDate('Y-m-d', $this->getDefaultFromDate->execute());
        }

        if ($from) {
            $from = $this->getLocalDate->execute($from);
            $select = $object instanceof Select ? $object : $object->getSelect();
            $select->where(sprintf('%s.%s >= ?', $tablePrefix, $dateFiled), $from);
        }
    }
}
