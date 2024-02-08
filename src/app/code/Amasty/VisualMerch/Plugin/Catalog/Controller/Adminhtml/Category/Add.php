<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Catalog\Controller\Adminhtml\Category;

use Magento\Catalog\Controller\Adminhtml\Category\Add as AddController;

class Add
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider
     */
    private $dataProvider;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $dataProvider
    ) {
        $this->registry = $registry;
        $this->dataProvider = $dataProvider;
    }

    /**
     * @param AddController $controller
     * @param $result
     * @return mixed
     */
    public function afterExecute(AddController $controller, $result)
    {
        $category = $this->registry->registry('current_category');
        $this->dataProvider->setCategoryId((int)$category->getId());
        $this->dataProvider->clear();
        $this->dataProvider->init($category);
        return $result;
    }
}
