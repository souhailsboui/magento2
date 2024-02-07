<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Product;

use Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver;
use Amasty\VisualMerch\Model\ResourceModel\Product\Collection;
use Amasty\VisualMerch\Model\RuleFactory;
use Magento\Catalog\Api\Data\CategoryInterface;

class AdminhtmlDataProvider extends \Magento\Framework\Model\AbstractModel
{
    public const DEFAULT_PRODUCT = 0;
    public const DEFAULT_REQUEST_NAME = 'catalog_view_container';
    public const DEFAULT_REQUEST_LIMIT = 0;

    /**
     * @var  \Amasty\VisualMerch\Model\Adminhtml\Session
     */
    private $session;

    /**
     * @var \Amasty\VisualMerch\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Sorting
     */
    private $sorting;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $emulation;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $defaultStore;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Framework\Search\Request\Config
     */
    private $searchRequestConfig;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    private $stockStatus;

    /**
     * @var MatchedProductsResolver
     */
    private $matchedProductsResolver;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\Config $backendConfig,
        \Amasty\VisualMerch\Model\Adminhtml\Session $session,
        \Amasty\VisualMerch\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Amasty\VisualMerch\Model\Product\Sorting $sorting,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\VisualMerch\Model\RuleFactory $ruleFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockStatus,
        \Magento\Framework\Search\Request\Config $searchRequestConfig,
        MatchedProductsResolver $matchedProductsResolver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->session = $session;
        $this->backendConfig = $backendConfig;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sorting = $sorting;
        $this->moduleManager = $moduleManager;
        $this->emulation = $emulation;
        $this->defaultStore = current($storeManager->getStores());
        $this->ruleFactory = $ruleFactory;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->stockStatus = $stockStatus;
        $this->matchedProductsResolver = $matchedProductsResolver;
        $this->initSession();
    }

    /**
     * @param $conditions
     * @return $this;
     * @deprecated session storage is discourage.
     */
    public function setSerializedRuleConditions($conditions)
    {
        $this->session->setSerializedRuleConditions($conditions);

        return $this;
    }

    public function initSession()
    {
        $category = $this->getCurrentCategory();

        if ($category) {
            $this->setCategoryId((int)$category->getId());
        }
    }

    /**
     * @return string
     * @deprecated session storage is discourage.
     */
    public function getSerializedRuleConditions()
    {
        return $this->session->getSerializedRuleConditions();
    }

    /**
     * @return Collection
     */
    public function getProductCollection($storeId = null)
    {
        if (!$this->hasData('product_collection')) {
            $this->emulation->startEnvironmentEmulation($storeId ?: $this->getStoreId());
            $collection = $this->productCollectionFactory->create()
                ->addAttributeToSelect([
                    'sku',
                    'name',
                    'price',
                    'small_image'
                ]);

            $this->stockStatus->addStockDataToCollection($collection, false);

            $this->emulation->stopEnvironmentEmulation();
            $this->orderCollection($collection);
            $this->setData('product_collection', $collection);
        }

        return $this->getData('product_collection');
    }

    /**
     * @param Collection $collection
     * @return $this
     */
    private function orderCollection($collection)
    {
        $sourceCollection = $this->getSourceCollection();
        $allIds = $sourceCollection->getProductIds();
        $sortedIds = $this->sortIds($allIds);
        $ids = implode(',', $sortedIds);
        $collection->addIdFilter($sortedIds);
        $field = $sourceCollection->getSelect()->getAdapter()->quoteIdentifier('e.entity_id');

        if ($ids) {
            $collection->getSelect()->order(new \Zend_Db_Expr("FIELD({$field}, {$ids})"));
        }

        if ($this->getRestoreConditions()) {
            $this->restoreConditions($allIds);
        }

        return $this;
    }

    /**
     * @param bool $isRestore
     * @return $this
     * @deprecad variable removed. Use SerializedRuleConditions instead
     */
    public function setRestoreConditions($isRestore)
    {
        return $this;
    }

    /**
     * @return bool;
     */
    public function getRestoreConditions()
    {
        return (bool)$this->getSerializedRuleConditions();
    }

    /**
     * @param array $productIds
     * @return $this
     */
    private function restoreConditions(array $productIds)
    {
        $this->setCategoryProductIds($productIds);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDynamicMode()
    {
        return (bool)$this->session->getDisplayMode();
    }

    /**
     * @param bool $displayMode
     * @return $this
     */
    public function setDisplayMode($displayMode = false)
    {
        $this->session->setDisplayMode($displayMode);

        return $this;
    }

    private function getAnyVisibilitySourceCollection(?int $storeId = null): Collection
    {
        $storeId = $storeId ?? $this->getStoreId();
        $key = sprintf('source_collection_any_visibility_%d', $storeId);
        if (!$this->hasData($key)) {
            $collection = clone $this->getSourceCollection($storeId);
            $collection->clear();
            $collection->setIsVisibleOnlyFilter(false);
            $this->setData($key, $collection);
        }

        return $this->getData($key);
    }

    private function getSourceCollection(?int $storeId = null): Collection
    {
        $storeId = $storeId ?? $this->getStoreId();
        $key = sprintf('source_collection_%d', $storeId);
        if (!$this->hasData($key)) {
            $collection = $this->productCollectionFactory->create();
            $collection->setStoreId($storeId);

            if ($this->getRestoreConditions()) {
                $matchedProductIds = $this->matchedProductsResolver->execute(
                    $this->getCurrentCategory(),
                    $this->getSerializedRuleConditions()
                ) ?: [self::DEFAULT_PRODUCT];

                $collection->addIdFilter($matchedProductIds);

                if ($this->getDynamicCollectionLimit()) {
                    $collection->getSelect()->limit($this->getDynamicCollectionLimit());
                }
            } else {
                $collection->addIdFilter(array_merge([self::DEFAULT_PRODUCT], $this->getCategoryProductIds()));
                $collection->setUseDefaultSorting(true);
            }

            $collection->setIsVisibleOnlyFilter(true);

            $this->applyCollectionOrder($collection, $storeId);
            $this->setData($key, $collection);
        }

        return $this->getData($key);
    }

    private function applyCollectionOrder(Collection $collection, int $storeId): void
    {
        $this->sorting->applySorting($collection, $storeId, $this->getSortOrder());
    }

    /**
     * @param array $ids
     * @param array $sortedIds
     *
     * @return array
     */
    private function sortIds($ids, $sortedIds = [])
    {
        $sorted = $sortedIds ?: $this->preparePositionDataForSort($ids);
        $ids = array_diff($ids, $sorted);
        $itemsCount = count($ids) + count($sorted);
        $idx = 0;

        while ($idx < $itemsCount) {
            if (!isset($sorted[$idx]) && current($ids)) {
                $sorted[$idx] = current($ids);
                next($ids);
            }

            $idx++;
        }

        ksort($sorted, SORT_NUMERIC);

        return $sorted;
    }

    /**
     * @param array $ids
     * @return array
     */
    private function preparePositionDataForSort($ids)
    {
        $positionData = array_flip($this->getProductPositionData());
        $positionData = array_intersect($positionData, $ids);
        $maxPosition = count($ids);

        foreach ($positionData as $position => $productId) {
            if ($position > $maxPosition) {
                $positionData[$maxPosition] = $productId;
                $maxPosition--;
            }
        }

        return $positionData;
    }

    /**
     * @return CategoryInterface
     */
    public function initRule()
    {
        $category = $this->getCurrentCategory();
        $rule = $category->getAmastyRule();

        if (!$rule) {
            $rule = $this->ruleFactory->create();
            $conditions = $category->getData('amasty_dynamic_conditions');
            $category->setData('amasty_rule', $rule->setConditionsSerialized($conditions));
        }

        if ($this->getSerializedRuleConditions()) {
            $rule->setConditions([]);
            $rule->setData('conditions_serialized', $this->getSerializedRuleConditions());
        }

        return $rule;
    }

    /**
     * @return CategoryInterface
     */
    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * @return array
     */
    public function getProductPositionData()
    {
        return $this->session->getPositionData() ?: [];
    }

    /**
     * @param array $positionData
     * @return $this
     * @deprecated session storage is discourage.
     */
    public function setProductPositionData($positionData = [])
    {
        if (!empty($positionData)) {
            $currentPositionData = $this->session->getPositionData() ?? [];

            foreach ($positionData as $productId => $position) {
                $currentPositionData[$productId] = $position;
            }

            $positionData = $currentPositionData;
            $this->session->setPositionData($positionData);
        }

        return $this;
    }

    /**
     * @param $key
     * @return $this
     * @deprecated session storage is discourage.
     */
    public function unsetProductPositionData($key)
    {
        $data = $this->getProductPositionData();

        if (isset($data[$key])) {
            unset($data[$key]);
            $this->session->setPositionData($data);
        }

        return $this;
    }

    /**
     * @param int $sortOrder
     * @return $this
     * @deprecated session storage is discourage.
     */
    public function setSortOrder($sortOrder)
    {
        $this->session->setSortOrder($sortOrder);

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return (int)$this->session->getSortOrder();
    }

    /**
     * @param array $productIds
     * @return $this
     */
    public function setCategoryProductIds(array $productIds = [])
    {
        $positionData = $this->getProductPositionData();
        $diff = array_diff(array_keys($positionData), $productIds);

        foreach ($diff as $productId) {
            $this->resortPositionData($this->getCurrentProductPosition($productId));
            $this->unsetProductPositionData($productId);
        }

        $this->session->setCategoryProductIds($productIds);

        return $this;
    }

    /**
     * @param $productId
     * @return $this
     */
    public function unsetCategoryProductId($productId)
    {
        $productIds = $this->getCategoryProductIds();
        $flipped = array_flip($productIds);

        if (isset($flipped[$productId])) {
            unset($productIds[$flipped[$productId]]);
            $this->setCategoryProductIds($productIds);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getCategoryProductIds()
    {
        return $this->session->getCategoryProductIds() ?: [];
    }

    /**
     * @return int
     */
    public function getInvisibleProductsCount()
    {
        $allCount = $this->getAnyVisibilitySourceCollection()->getSize();

        $collectionProductIds = $this->getSourceCollection()->getProductIds();

        return $allCount - count($collectionProductIds);
    }

    /**
     * @param \Magento\Catalog\Model\Category $entity
     * @return $this
     * @deprecated session storage is discourage.
     */
    public function init($entity)
    {
        $this->setCategoryProductIds(array_keys($entity->getProductsPosition()));
        $this->setDisplayMode($entity->getData('amlanding_is_dynamic'));
        $this->setSortOrder($entity->getData('amasty_category_product_sort'));
        $this->session->setPositionData($entity->getProductPositionData());
        $this->setStoreId($entity->getStoreId());

        return $this;
    }

    /**
     * @param int $categoryId
     */
    public function setCategoryId(int $categoryId)
    {
        $this->session->setCurrentCategoryId($categoryId);
    }

    /**
     * @param int $sourcePosition
     * @param int $destanationPosition
     * @return $this
     */
    public function resortPositionData($sourcePosition, $destanationPosition = null)
    {
        $positionData = $this->getProductPositionData();

        if ($destanationPosition === null) {
            foreach ($positionData as $productId => $position) {
                if ($position > $sourcePosition) {
                    $positionData[$productId]--;
                }
            }
        } elseif ($sourcePosition < $destanationPosition) {
            foreach ($positionData as $productId => $position) {
                if ($position > $sourcePosition && $position <= $destanationPosition) {
                    $positionData[$productId]--;
                }
            }
        } elseif ($sourcePosition > $destanationPosition) {
            foreach ($positionData as $productId => $position) {
                if ($position >= $destanationPosition && $position < $sourcePosition) {
                    $positionData[$productId]++;
                }
            }
        } else {
            return $this;
        }

        $this->session->setPositionData($positionData);

        return $this;
    }

    /**
     * @param int $productId
     * @return int
     */
    public function getCurrentProductPosition($productId)
    {
        $productIds = $this->getSourceCollection()->getProductIds();
        $productIds = $this->sortIds($productIds);
        $position = array_search($productId, $productIds);

        return $position !== false ? $position : count($productIds);
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->session->setStoreId($storeId);

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->session->getStoreId() ? $this->session->getStoreId() : $this->defaultStore->getId();
    }

    public function getFullPositionDataByStoreId(int $storeId): array
    {
        $anyVisibilityProducts = $this->getAnyVisibilitySourceCollection($storeId)->getProductIds();
        $anyVisibilityProducts = $this->sortIds($anyVisibilityProducts);
        return array_flip($anyVisibilityProducts);
    }

    /**
     * Clear storage data after save category
     *
     * @return $this
     */
    public function clear()
    {
        $this->session->setPositionData(null);
        $this->session->setCategoryProductIds(null);
        $this->setSerializedRuleConditions(null);
        $this->setSortOrder(null);
        $this->setStoreId(null);

        return $this;
    }

    /**
     * @return int
     */
    private function getDynamicCollectionLimit()
    {
        $requestData = $this->searchRequestConfig->get(self::DEFAULT_REQUEST_NAME);

        return isset($requestData['size']) ? $requestData['size'] : self::DEFAULT_REQUEST_LIMIT;
    }
}
