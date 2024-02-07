<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Catalog\Model\Category\DataProvider;

use Amasty\VisualMerch\Model\Category\ResolveRootCategoryId;
use Amasty\VisualMerch\Model\DynamicCategory\Store\GetStoresForRootCategory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category\DataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;

class AddNonStoreCategoryWarning
{
    /**
     * @var ResolveRootCategoryId
     */
    private $resolveRootCategoryId;

    /**
     * @var GetStoresForRootCategory
     */
    private $getStoresForRootCategory;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(
        ResolveRootCategoryId $resolveRootCategoryId,
        GetStoresForRootCategory $getStoresForRootCategory,
        ArrayManager $arrayManager,
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->resolveRootCategoryId = $resolveRootCategoryId;
        $this->getStoresForRootCategory = $getStoresForRootCategory;
        $this->arrayManager = $arrayManager;
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @see DataProvider::getMeta
     */
    public function afterGetMeta(DataProvider $subject, array $meta): array
    {
        $category = $subject->getCurrentCategory();
        if ($category && !$this->isRootCategoryAssignedToSomeStore($category)) {
            $meta = $this->arrayManager->set(
                'products_fieldset/children/amasty_merch_category_unassigned/arguments/data/config',
                $meta,
                [
                    'componentType' => 'container',
                    'component' => 'Magento_Ui/js/form/components/html',
                    'content' => 'Please note: products displaying is not available until category '
                        . 'is assigned to store view',
                    'sortOrder' => 5,
                    'additionalClasses' => 'ammerch-products-update-message message message-info',
                    'imports' => [
                        'visible' => 'index = amlanding_is_dynamic:checked',
                        '__disableTmpl' => ['visible' => false]
                    ]
                ]
            );
        }

        return $meta;
    }

    private function isRootCategoryAssignedToSomeStore(CategoryInterface $category): bool
    {
        if (!$category->getId()) {
            if ($parentId = $this->request->getParam('parent')) {
                try {
                    $category = $this->categoryRepository->get($parentId);
                } catch (NoSuchEntityException $e) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return !empty($this->getStoresForRootCategory->execute(
            $this->resolveRootCategoryId->execute($category)
        ));
    }
}
