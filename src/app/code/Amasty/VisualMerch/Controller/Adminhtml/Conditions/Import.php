<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Controller\Adminhtml\Conditions;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Import extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Magento_Catalog::categories';

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider
     */
    private $adminhtmlDataProvider;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $adminhtmlDataProvider,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        $this->adminhtmlDataProvider = $adminhtmlDataProvider;
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Catalog\Api\Data\CategoryInterface|\Magento\Catalog\Model\Category
     */
    private function initCategory()
    {
        $categoryId = $this->resolveCategoryId();
        $storeId = $this->getRequest()->getParam('store', 0);

        try {
            $category = $this->categoryRepository->get($categoryId);
        } catch (NoSuchEntityException $e) {
            $category = $this->categoryFactory->create();
        }

        $category->setStoreId($storeId);
        $this->registry->register('category', $category);
        $this->registry->register('current_category', $category);

        return $category;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $category = $this->initCategory();
        $this->adminhtmlDataProvider->setCategoryId((int)$category->getId());

        try {
            $category = $this->categoryRepository->get($this->getRequest()->getParam('source_id'));
            $this->adminhtmlDataProvider->setSerializedRuleConditions(
                $category->getData('amasty_dynamic_conditions')
            );
        } catch (NoSuchEntityException $e) {
            ;//do nothing here
        }

        $block = $this->layoutFactory->create()->createBlock(
            \Amasty\VisualMerch\Block\Adminhtml\Conditions\Form::class,
            'conditions.form'
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($block->toHtml());
    }

    /**
     * Resolve Category Id (from get or from post)
     *
     * @return int
     */
    private function resolveCategoryId()
    {
        $categoryId = $this->getRequest()->getParam('id', 0);
        return $categoryId ?: $this->getRequest()->getParam('entity_id', 0);
    }
}
