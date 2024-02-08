<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Controller\Adminhtml\Product;

class Search extends ControllerAbstract
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->initCategory();

        $searchQuery = $this->getRequest()->getParam('search_query');
        $storeId = $this->getRequest()->getParam('store', $this->dataProvider->getStoreId());
        $sortOrder = $this->getRequest()->getParam('sort_order', false);
        $this->dataProvider->setSortOrder($sortOrder);
        $this->dataProvider->setStoreId($storeId);

        $block = $this->layoutFactory->create()->createBlock(
            \Amasty\VisualMerch\Block\Adminhtml\Products\Listing::class,
            'product.listing'
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $block->search($searchQuery)->toHtml()
        );
    }
}
