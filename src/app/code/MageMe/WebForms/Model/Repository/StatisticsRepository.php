<?php

namespace MageMe\WebForms\Model\Repository;

use Exception;
use MageMe\WebForms\Api\Data\StatisticsInterface;
use MageMe\WebForms\Api\StatisticsRepositoryInterface;
use MageMe\WebForms\Api\StatisticsSearchResultInterface;
use MageMe\WebForms\Api\StatisticsSearchResultInterfaceFactory;
use MageMe\WebForms\Model\ResourceModel\Statistics as ResourceStatistics;
use MageMe\WebForms\Model\ResourceModel\Statistics\CollectionFactory as StatisticsCollectionFactory;
use MageMe\WebForms\Model\StatisticsFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class StatisticsRepository implements StatisticsRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var StatisticsFactory
     */
    protected $statisticsFactory;

    /**
     * @var ResourceStatistics
     */
    protected $resource;

    /**
     * @var StatisticsCollectionFactory
     */
    protected $statisticsCollectionFactory;

    /**
     * @var StatisticsSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StatisticsSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param StatisticsCollectionFactory $statisticsCollectionFactory
     * @param ResourceStatistics $resource
     * @param StatisticsFactory $statisticsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SearchCriteriaBuilder                  $searchCriteriaBuilder,
        StatisticsSearchResultInterfaceFactory $searchResultInterfaceFactory,
        StatisticsCollectionFactory            $statisticsCollectionFactory,
        ResourceStatistics                     $resource,
        StatisticsFactory                      $statisticsFactory,
        CollectionProcessorInterface           $collectionProcessor
    ) {
        $this->collectionProcessor         = $collectionProcessor;
        $this->statisticsFactory           = $statisticsFactory;
        $this->resource                    = $resource;
        $this->statisticsCollectionFactory = $statisticsCollectionFactory;
        $this->searchResultFactory         = $searchResultInterfaceFactory;
        $this->searchCriteriaBuilder       = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id): StatisticsInterface
    {
        $statistic = $this->statisticsFactory->create();
        $this->resource->load($statistic, $id);
        if (!$statistic->getId()) {
            throw new NoSuchEntityException(__('Unable to find stat with ID "%1"', $id));
        }
        return $statistic;
    }

    /**
     * @inheritDoc
     */
    public function save(StatisticsInterface $statistic): StatisticsInterface
    {
        try {
            $this->resource->save($statistic);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $statistic;
    }

    /**
     * @inheritDoc
     */
    public function delete(StatisticsInterface $statistic): bool
    {
        try {
            $this->resource->delete($statistic);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StatisticsSearchResultInterface
    {
        $collection = $this->statisticsCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function getListByEntity(string $entityType, int $entityId): StatisticsSearchResultInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StatisticsInterface::ENTITY_TYPE, $entityType)
            ->addFilter(StatisticsInterface::ENTITY_ID, $entityId)
            ->create();
        return $this->getList($searchCriteria);
    }
}