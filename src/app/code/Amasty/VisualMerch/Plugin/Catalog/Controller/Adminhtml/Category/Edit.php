<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Catalog\Controller\Adminhtml\Category;

use Magento\Catalog\Controller\Adminhtml\Category\Edit as EditController;

class Edit
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider
     */
    private $dataProvider;

    /**
     * @var \Amasty\VisualMerch\Model\ResourceModel\Product
     */
    private $productPositionDataResource;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $dataProvider,
        \Amasty\VisualMerch\Model\ResourceModel\Product $productPositionDataResource
    ) {
        $this->registry = $registry;
        $this->dataProvider = $dataProvider;
        $this->productPositionDataResource = $productPositionDataResource;
    }

    /**
     * @param EditController $controller
     * @param $result
     * @return mixed
     */
    public function afterExecute(EditController $controller, $result)
    {
        $category = $this->registry->registry('current_category');
        $this->productPositionDataResource->loadProductPositionData($category);
        $this->dataProvider->setCategoryId((int)$category->getId());
        $this->dataProvider->clear();
        $this->dataProvider->init($category);
        return $result;
    }
}
