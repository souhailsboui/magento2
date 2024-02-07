<?php

namespace MageMe\WebForms\Api;

use MageMe\WebForms\Api\Data\StatisticsInterface;
use MageMe\WebForms\Model\Statistics;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface StatisticsRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return StatisticsInterface|Statistics
     * @throws NoSuchEntityException
     * @noinspection PhpDocSignatureInspection
     */
    public function getById(int $id): StatisticsInterface;

    /**
     * @param StatisticsInterface|Statistics $statistic
     * @return StatisticsInterface
     * @throws CouldNotSaveException
     * @noinspection PhpDocSignatureInspection
     */
    public function save(StatisticsInterface $statistic): StatisticsInterface;

    /**
     * @param StatisticsInterface|Statistics $statistic
     * @return bool
     * @throws CouldNotDeleteException
     * @noinspection PhpDocSignatureInspection
     */
    public function delete(StatisticsInterface $statistic): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return StatisticsSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StatisticsSearchResultInterface;

    /**
     * @param string $entityType
     * @param int $entityId
     * @return StatisticsSearchResultInterface
     */
    public function getListByEntity(string $entityType, int $entityId): StatisticsSearchResultInterface;

}