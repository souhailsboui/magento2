<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Controller\Adminhtml\Product;

class Add extends ControllerAbstract
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->initCategory();

        $productIds = $this->getRequest()->getParam('product_ids', []);
        $this->dataProvider->setCategoryProductIds(array_keys($productIds));

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([]);
        return $resultJson;
    }
}
