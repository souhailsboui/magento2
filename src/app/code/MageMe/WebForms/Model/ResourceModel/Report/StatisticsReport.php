<?php

namespace MageMe\WebForms\Model\ResourceModel\Report;

use MageMe\WebForms\Helper\StatisticsHelper;
use MageMe\WebForms\Setup\Table\StatisticsTable;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Sales\Model\ResourceModel\Report\AbstractReport;
use Psr\Log\LoggerInterface;

class StatisticsReport extends AbstractReport
{
    /**
     * @var StatisticsHelper
     */
    private $statisticsHelper;

    /**
     * @param StatisticsHelper $statisticsHelper
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param Validator $timezoneValidator
     * @param DateTime $dateTime
     * @param $connectionName
     */
    public function __construct(
        StatisticsHelper  $statisticsHelper,
        Context           $context,
        LoggerInterface   $logger,
        TimezoneInterface $localeDate,
        FlagFactory       $reportsFlagFactory,
        Validator         $timezoneValidator,
        DateTime          $dateTime,
                          $connectionName = null
    ) {
        parent::__construct($context, $logger, $localeDate, $reportsFlagFactory, $timezoneValidator, $dateTime,
            $connectionName);
        $this->statisticsHelper = $statisticsHelper;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(StatisticsTable::TABLE_NAME, 'id');
    }

    /**
     * @param $from
     * @param $to
     * @return $this
     * @noinspection PhpUnusedParameterInspection
     */
    public function aggregate($from = null, $to = null): StatisticsReport
    {
        $this->statisticsHelper->calculateFormStatistics();
        return $this;
    }
}