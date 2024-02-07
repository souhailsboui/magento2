<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Controller\Adminhtml\Product;

class Mode extends ControllerAbstract
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $category = $this->initCategory();
        $this->dataProvider->setCategoryId((int)$category->getId());
        $this->dataProvider->init($category);
        $this->dataProvider->setDisplayMode($this->getRequest()->getParam('mode', false));

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([]);
        return $resultJson;
    }
}
