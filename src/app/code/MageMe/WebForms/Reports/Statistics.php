<?php

namespace MageMe\WebForms\Reports;

use MageMe\WebForms\Helper\StatisticsHelper;

class Statistics
{
    /**
     * @var StatisticsHelper
     */
    private $statisticsHelper;

    /**
     * @param StatisticsHelper $statisticsHelper
     */
    public function __construct(StatisticsHelper $statisticsHelper)
    {
        $this->statisticsHelper = $statisticsHelper;
    }

    /**
     * @return void
     */
    public function aggregate(): void
    {
        $this->statisticsHelper->calculateFormStatistics();
    }
}