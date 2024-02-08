<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Helper;

use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\StatisticsInterface;
use MageMe\WebForms\Api\StatisticsRepositoryInterface;
use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use MageMe\WebForms\Helper\Statistics\FormStat;
use MageMe\WebForms\Helper\Statistics\ResultStat;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Model\AbstractModel;

class StatisticsHelper
{
    const PATH_ENABLE = 'webforms/statistics/enable';
    const PATH_CRON_ENABLE = 'webforms/statistics/cron_enable';
    const PATH_SHOW_NULL_STATS = 'webforms/statistics/show_null_stats';
    const PATH_SHOW_UNREAD_BADGE = 'webforms/statistics/show_unread_badge';
    const PATH_ENABLED_STATS = 'webforms/statistics/enabled_stats';
    const IS_UNREAD_REPLY = 'is_unread_reply';
    const STATISTICS = 'statistics';
    /**
     * @var array
     */
    private $enabledStats;
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var FormStat
     */
    private $formStat;
    /**
     * @var ResultStat
     */
    private $resultStat;
    /**
     * @var StatisticsRepositoryInterface
     */
    private $statisticsRepository;

    /**
     * @param StatisticsRepositoryInterface $statisticsRepository
     * @param ResultStat $resultStat
     * @param FormStat $formStat
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        StatisticsRepositoryInterface $statisticsRepository,
        ResultStat                    $resultStat,
        FormStat                      $formStat,
        ScopeConfigInterface          $config
    ) {
        $this->config               = $config;
        $this->formStat             = $formStat;
        $this->resultStat           = $resultStat;
        $this->statisticsRepository = $statisticsRepository;
    }

    /**
     * @return bool
     */
    public function getConfigStatisticEnabled(): bool
    {
        return (bool)$this->config->getValue(self::PATH_ENABLE);
    }

    /**
     * @return bool
     */
    public function getConfigStatisticCronEnabled(): bool
    {
        return (bool)$this->config->getValue(self::PATH_CRON_ENABLE);
    }

    /**
     * @return bool
     */
    public function getConfigStatisticShowNullStats(): bool
    {
        return (bool)$this->config->getValue(self::PATH_SHOW_NULL_STATS);
    }

    /**
     * @return bool
     */
    public function getConfigStatisticShowUnreadBadge(): bool
    {
        return (bool)$this->config->getValue(self::PATH_SHOW_UNREAD_BADGE);
    }

    /**
     * @return array
     */
    public function getConfigStatisticEnabledStats(): array
    {
        $value = (string)$this->config->getValue(self::PATH_ENABLED_STATS);
        return $value ? explode(',', $value) : [];
    }

    /**
     * @return FormStat
     */
    public function getFormStatistics(): FormStat
    {
        return $this->formStat;
    }

    /**
     * @return ResultStat
     */
    public function getResultStatistics(): ResultStat
    {
        return $this->resultStat;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function getStatEnabled(string $code): bool
    {
        if (!$this->enabledStats) {
            $this->enabledStats = $this->getConfigStatisticEnabledStats();
        }
        return in_array($code, $this->enabledStats);
    }

    /**
     * @return void
     */
    public function calculateFormStatistics(): void
    {
        if ($this->getStatEnabled(FormStat::RESULT_ALL)) {
            $this->formStat->calculateFormAllResultCount();
        }
        if ($this->getStatEnabled(FormStat::RESULT_UNREAD)) {
            $this->formStat->calculateFormUnreadResultCount();
        }
        if ($this->getStatEnabled(FormStat::RESULT_REPLIED)) {
            $this->formStat->calculateFormRepliedResultCount();
        }
        if ($this->getStatEnabled(FormStat::RESULT_FOLLOW_UP)) {
            $this->resultStat->calculateResultUnreadMessagesCount();
            $this->formStat->calculateFormUnreadMessagesCount();
            $this->formStat->calculateFormFollowUpCount();
        }
        if ($this->getStatEnabled(FormStat::RESULT_STATUS_NOT_APPROVED)) {
            $this->formStat->calculateFormResultStatusCount(ApprovalStatus::STATUS_NOT_APPROVED);
        }
        if ($this->getStatEnabled(FormStat::RESULT_STATUS_PENDING)) {
            $this->formStat->calculateFormResultStatusCount(ApprovalStatus::STATUS_PENDING);
        }
        if ($this->getStatEnabled(FormStat::RESULT_STATUS_APPROVED)) {
            $this->formStat->calculateFormResultStatusCount(ApprovalStatus::STATUS_APPROVED);
        }
        if ($this->getStatEnabled(FormStat::RESULT_STATUS_COMPLETED)) {
            $this->formStat->calculateFormResultStatusCount(ApprovalStatus::STATUS_COMPLETED);
        }
    }

    /**
     * @param string $entityType
     * @param string $entityKey
     * @return string
     */
    public function getJsonStatSql(string $entityType, string $entityKey): string
    {
        return $this->formStat->getJsonStatSql($entityType, $entityKey);
    }

    /**
     * @return bool
     */
    public function showBadge(): bool
    {
        return $this->getConfigStatisticShowUnreadBadge()
            && $this->getStatEnabled(FormStat::RESULT_UNREAD);
    }

    /**
     * @param ResultInterface|AbstractModel $result
     * @return void
     */
    public function processResultAfterSave($result): void
    {
        if ($this->getStatEnabled(FormStat::RESULT_UNREAD)) {
            if ((bool)$result->getOrigData(ResultInterface::IS_READ) != (bool)$result->getIsRead()) {
                if ($result->getIsRead()) {
                    $this->formStat->decStatValue($result->getFormId(), FormStat::RESULT_UNREAD);
                } else {
                    $this->formStat->incStatValue($result->getFormId(), FormStat::RESULT_UNREAD);
                }
            }
        }
        if ($this->getStatEnabled(FormStat::RESULT_REPLIED)) {
            if ((bool)$result->getOrigData(ResultInterface::IS_REPLIED) != (bool)$result->getIsReplied()) {
                if ($result->getIsReplied()) {
                    $this->formStat->incStatValue($result->getFormId(), FormStat::RESULT_REPLIED);
                } else {
                    $this->formStat->decStatValue($result->getFormId(), FormStat::RESULT_REPLIED);
                }
            }
        }
        if ((int)$result->getOrigData(ResultInterface::APPROVED) != (int)$result->getApproved()) {
            $statusCode = $this->formStat->getStatusCode($result->getApproved());
            if ($this->getStatEnabled($statusCode)) {
                $this->formStat->incStatValue($result->getFormId(), $statusCode);
            }
            $oldStatusCode = (int)$result->getOrigData(ResultInterface::APPROVED);
            if ($this->getStatEnabled($oldStatusCode)) {
                $this->formStat->decStatValue($result->getFormId(), $oldStatusCode);
            }
        }
    }

    /**
     * @param ResultInterface|AbstractModel $result
     * @return void
     */
    public function processNewResultAfterSave($result): void
    {
        if ($this->getStatEnabled(FormStat::RESULT_ALL)) {
            $this->formStat->incStatValue($result->getFormId(), FormStat::RESULT_ALL);
        }
        if ($this->getStatEnabled(FormStat::RESULT_UNREAD) && !$result->getIsRead()) {
            $this->formStat->incStatValue($result->getFormId(), FormStat::RESULT_UNREAD);
        }
        if ($this->getStatEnabled(FormStat::RESULT_REPLIED) && $result->getIsReplied()) {
            $this->formStat->incStatValue($result->getFormId(), FormStat::RESULT_REPLIED);
        }
        $statusCode = $this->formStat->getStatusCode($result->getApproved());
        if ($this->getStatEnabled($statusCode)) {
            $this->formStat->incStatValue($result->getFormId(), $statusCode);
        }
    }

    /**
     * @param ResultInterface|AbstractModel $result
     * @return void
     */
    public function processResultAfterDelete($result): void
    {
        if ($this->getStatEnabled(FormStat::RESULT_ALL)) {
            $this->formStat->decStatValue($result->getFormId(), FormStat::RESULT_ALL);
        }
        if ($this->getStatEnabled(FormStat::RESULT_UNREAD) && !$result->getIsRead()) {
            $this->formStat->decStatValue($result->getFormId(), FormStat::RESULT_UNREAD);
        }
        if ($this->getStatEnabled(FormStat::RESULT_REPLIED) && $result->getIsReplied()) {
            $this->formStat->decStatValue($result->getFormId(), FormStat::RESULT_REPLIED);
        }
        $statusCode = $this->formStat->getStatusCode($result->getApproved());
        if ($this->getStatEnabled($statusCode)) {
            $this->formStat->decStatValue($result->getFormId(), $statusCode);
        }

        /** @var StatisticsInterface $stat */
        foreach ($this->statisticsRepository->getListByEntity(ResultStat::ENTITY_TYPE,
            $result->getId())->getItems() as $stat) {
            if ($stat->getCode() == ResultStat::CUSTOMER_MESSAGE_UNREAD && $this->getStatEnabled(FormStat::RESULT_FOLLOW_UP)) {
                $msgCount = (int)$stat->getValue();
                if ($msgCount > 0) {
                    $this->formStat->decStatValue($result->getFormId(), FormStat::RESULT_MESSAGE_UNREAD, $msgCount);
                    $this->formStat->decStatValue($result->getFormId(), FormStat::RESULT_FOLLOW_UP);
                }
            }
            try {
                $this->statisticsRepository->delete($stat);
            } catch (CouldNotDeleteException $e) {
                continue;
            }
        }
    }

    /**
     * @param MessageInterface|AbstractModel $message
     * @return void
     */
    public function processMessageAfterSave($message): void
    {
        if ($this->getStatEnabled(FormStat::RESULT_FOLLOW_UP) && $message->getIsFromCustomer()) {
            if ((bool)$message->getOrigData(ResultInterface::IS_READ) != (bool)$message->getIsRead()) {
                if ($message->getIsRead()) {
                    $this->resultStat->decStatValue($message->getResultId(), ResultStat::CUSTOMER_MESSAGE_UNREAD);
                    $this->formStat->decStatValue($message->getResult()->getFormId(), FormStat::RESULT_MESSAGE_UNREAD);
                } else {
                    $this->resultStat->incStatValue($message->getResultId(), ResultStat::CUSTOMER_MESSAGE_UNREAD);
                    $this->formStat->incStatValue($message->getResult()->getFormId(), FormStat::RESULT_MESSAGE_UNREAD);
                }
                $this->formStat->calculateFormFollowUpCount($message->getResult()->getFormId());
            }
        }
    }

    /**
     * @param MessageInterface|AbstractModel $message
     * @return void
     */
    public function processNewMessageAfterSave($message): void
    {
        if ($this->getStatEnabled(FormStat::RESULT_FOLLOW_UP)) {
            if ($message->getIsFromCustomer() && !$message->getIsRead()) {
                $this->resultStat->incStatValue($message->getResultId(), ResultStat::CUSTOMER_MESSAGE_UNREAD);
                $this->formStat->incStatValue($message->getResult()->getFormId(), FormStat::RESULT_MESSAGE_UNREAD);
                $this->formStat->calculateFormFollowUpCount($message->getResult()->getFormId());
            }
        }
    }

    /**
     * @param MessageInterface|AbstractModel $message
     * @return void
     */
    public function processMessageAfterDelete($message): void
    {
        if ($this->getStatEnabled(FormStat::RESULT_FOLLOW_UP)) {
            if ($message->getIsFromCustomer() && !$message->getIsRead()) {
                $this->resultStat->decStatValue($message->getResultId(), ResultStat::CUSTOMER_MESSAGE_UNREAD);
                $this->formStat->decStatValue($message->getResult()->getFormId(), FormStat::RESULT_MESSAGE_UNREAD);
                $this->formStat->calculateFormFollowUpCount($message->getResult()->getFormId());
            }
        }
    }
}
