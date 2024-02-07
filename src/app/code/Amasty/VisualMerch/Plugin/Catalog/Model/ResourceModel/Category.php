<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Catalog\Model\ResourceModel;

use Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider;
use Amasty\VisualMerch\Model\RuleFactory;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class Category
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var AdminhtmlDataProvider
     */
    private $adminhtmlDataProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        RuleFactory $ruleFactory,
        Request $request,
        AdminhtmlDataProvider $adminhtmlDataProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->request = $request;
        $this->adminhtmlDataProvider = $adminhtmlDataProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CategoryResource $subject, CategoryModel $category): array
    {
        $this->adminhtmlDataProvider->setCategoryId((int)$category->getId());
        $isDynamicCategory = (bool)$category->getData('amlanding_is_dynamic');

        if ($this->request->getControllerName() == 'category') {
            if ($isDynamicCategory && $this->isMassAction()) {
                throw new LocalizedException(
                    __(
                        'Category #%1 is dynamic. Please go to Category Edit Page for making any changes.',
                        $category->getId()
                    )
                );
            }

            if (!$this->isMassAction()) {
                $rule = $this->request->getParam('rule');
                if (is_array($rule) && isset($rule['conditions'])) {
                    $conditions = $rule['conditions'];
                    $conditionsSerialised = $this->ruleFactory->create()
                        ->loadPost(['conditions' => $conditions])
                        ->beforeSave()
                        ->getConditionsSerialized();
                    $category->setData('amasty_dynamic_conditions', $conditionsSerialised);
                }
                $category->setProductPositionData($this->adminhtmlDataProvider->getProductPositionData());
                $category->setData('amasty_category_product_sort', $this->adminhtmlDataProvider->getSortOrder());
                if (!$isDynamicCategory) {
                    $this->assignCategoryProducts($category);
                }
                $category->unsetData('amlanding_page_id');
            }
        }

        return [$category];
    }

    private function isMassAction(): bool
    {
        return $this->request->getControllerName() == 'massaction';
    }

    private function assignCategoryProducts(CategoryModel $category): void
    {
        $productIds = [];
        $stores = $this->storeManager->getStores();
        $parentIds = $category->getParentIds();
        // cycle need correctly save invisible products on current store
        foreach ($stores as $store) {
            if (in_array($store->getRootCategoryId(), $parentIds)
                || $store->getRootCategoryId() == $category->getId()
                || (empty($parentIds) && $category->isObjectNew())
            ) {
                $productPositionData = $this->adminhtmlDataProvider->getFullPositionDataByStoreId((int)$store->getId());
                $productIds = array_replace($productPositionData, $productIds);
            }
        }

        $category->setPostedProducts($productIds);
    }
}
