<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Block\Adminhtml\Widget\Select;

use Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider;

class Categories extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Amasty\VisualMerch\Model\Product\Sorting
     */
    private $categories;

    /**
     * @var AdminhtmlDataProvider
     */
    private $dataProvider;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\VisualMerch\Model\Source\Category $categories,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $dataProvider,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categories = $categories;
        $this->dataProvider = $dataProvider;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->registry = $registry;
    }

    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_VisualMerchUi::widget/select.phtml');
    }

    /**
     * @return array
     */
    public function getSelectOptions()
    {
        return $this->categories->toArray();
    }

    /**
     * @param $optionValue
     * @return bool
     */
    public function getIsOptionDisabled($optionValue)
    {
        $categoryIds = $this->getNonDynamicCategoryIds();
        return in_array($optionValue, $categoryIds);
    }

    /**
     * @return array
     */
    public function getNonDynamicCategoryIds()
    {
        if (!$this->hasData('non_dynamic_category_ids')) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->addAttributeToSelect('amlanding_is_dynamic', true);
            $categoryIds = [];
            foreach ($collection as $category) {
                if (!$category->getData('amlanding_is_dynamic')) {
                    $categoryIds[] = $category->getId();
                }
            }
            if ($category = $this->registry->registry('current_category')) {
                $categoryIds[] = $category->getId();
            }
            $this->setData('non_dynamic_category_ids', $categoryIds);
        }
        return $this->getData('non_dynamic_category_ids');
    }
}
