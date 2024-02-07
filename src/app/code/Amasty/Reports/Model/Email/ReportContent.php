<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\Email;

use Amasty\Reports\Api\Data\NotificationInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ReportContent
{
    public const BY_PRODUCT_REPORT = 'by_product';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $reportsListing;

    /**
     * @var CsvGenerator
     */
    private $csvGenerator;

    /**
     * @var int
     */
    private $ruleId = 0;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        RequestInterface $request,
        CsvGenerator $csvGenerator,
        DateTime $dateTime,
        array $reportsListing = []
    ) {
        $this->request = $request;
        $this->reportsListing = $reportsListing;
        $this->csvGenerator = $csvGenerator;
        $this->dateTime = $dateTime;
    }

    public function getContent(NotificationInterface $notification, string $report, string $storeId): string
    {
        $content = '';
        $report = $this->prepareReportIdentifier($report);
        if (!isset($this->reportsListing[$report])) {
            return $content;
        }

        $this->prepareRequest($notification, $report, $storeId);

        return $this->csvGenerator->getCsvContent($report);
    }

    private function prepareReportIdentifier(string $report): string
    {
        if ((int)$report) {
            $this->ruleId = $report;
            $report = self::BY_PRODUCT_REPORT;
        }

        return $report;
    }

    private function prepareRequest(NotificationInterface $notification, string $report, string $storeId)
    {
        $params = $this->request->getParams();

        $params['from'] = $this->dateTime->date('Y-m-d', $this->resolveDateShiftByInterval($notification));
        $params['to'] = $this->dateTime->date('Y-m-d');
        $params['store'] = $storeId;
        $params['interval'] = $notification->getDisplayPeriod();
        $params['namespace'] = $this->reportsListing[$report];
        if ($this->ruleId) {
            $params['rule'] = $this->ruleId;
        }
        $this->request->setParams($params);
    }

    private function resolveDateShiftByInterval(NotificationInterface $notification): string
    {
        $intervalQty = $notification->getIntervalQty();
        switch ($notification->getInterval()) {
            case \Amasty\Reports\Model\Source\Date\Interval::MONTH:
                return sprintf('now -%smonth', $intervalQty);
            case \Amasty\Reports\Model\Source\Date\Interval::YEAR:
                return sprintf('now -%syear', $intervalQty);
            default:
                return sprintf('now -%sday', $intervalQty);
        }
    }
}
