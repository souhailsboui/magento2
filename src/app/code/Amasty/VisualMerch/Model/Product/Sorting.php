<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Product;

use Amasty\VisualMerch\Model\Product\Sorting\Factory as SortingFactory;
use Amasty\VisualMerch\Model\Product\Sorting\ImprovedSorting\DummyMethod;
use Amasty\VisualMerch\Model\Product\Sorting\ImprovedSorting\MethodBuilder;
use Amasty\VisualMerch\Model\Product\Sorting\NewestTop;
use Amasty\VisualMerch\Model\Product\Sorting\SortInterface;
use Amasty\VisualMerch\Model\Product\Sorting\UserDefined;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Model\StoreManagerInterface;

class Sorting
{
    /**
     * @var array
     */
    protected $sortMethods = [
        'UserDefined',
        'OutStockBottom',
        'NewestTop',
        'NameAscending',
        'NameDescending',
        'PriceAscending',
        'PriceDescending',
    ];

    /**
     * @var SortingFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $sortInstances = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param SortingFactory $factory
     */
    public function __construct(
        SortingFactory $factory,
        MethodBuilder $improvedMethodBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->factory = $factory;
        foreach ($this->sortMethods as $className) {
            $this->sortInstances[] = $this->factory->create($className);
        }
        foreach ($improvedMethodBuilder->getMethodList() as $method) {
            $this->sortInstances[] = $method;
        }
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getSortingOptions()
    {
        $options = $default = $improved = [];
        foreach ($this->sortInstances as $idx => $instance) {
            if ($instance instanceof DummyMethod) {
                $improved[$idx] = $instance->getLabel();
            } elseif ($instance instanceof UserDefined) {
                $options[$idx] = $instance->getLabel();
            } else {
                $default[$idx] = $instance->getLabel();
            }
        }

        $options[__('Default Sorting')->render()] = $default;
        if ($improved) {
            $options[__('Improved Sorting')->render()] = $improved;
        } else {
            $options[__('Improved Sorting (not installed)')->render()] = [];
        }

        return $options;
    }

    /**
     * Get the instance of the first option which is None
     *
     * @param int $sortOption
     * @return SortInterface|null
     */
    public function getSortingInstance($sortOption)
    {
        if (isset($this->sortInstances[$sortOption])) {
            return $this->sortInstances[$sortOption];
        }
        return $this->sortInstances[0];
    }

    public function applySorting(Collection $collection, int $storeId, ?int $sortingMethod = null): Collection
    {
        $prevStoreId = $this->storeManager->getStore()->getId();
        $this->storeManager->setCurrentStore($storeId);

        $sortBuilder = $this->getSortingInstance($sortingMethod);
        $sortedCollection = $sortBuilder->sort($collection);

        if (!($sortBuilder instanceof NewestTop)) {
            $sortedCollection->addOrder('entity_id', Collection::SORT_ORDER_ASC);
        }

        if ($sortedCollection->isLoaded()) {
            $sortedCollection->clear();
        }

        $this->storeManager->setCurrentStore($prevStoreId);

        return $sortedCollection;
    }
}
