<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Controller\Adminhtml\Product;

use Amasty\VisualMerch\Model\DynamicCategory\Temporary\MatchedProductsResolver\DeleteIds as DeleteTemporaryMatches;

class Save extends ControllerAbstract
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Amasty\VisualMerch\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var DeleteTemporaryMatches
     */
    private $deleteTemporaryMatches;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $dataProvider,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Amasty\VisualMerch\Model\RuleFactory $ruleFactory,
        \Amasty\Base\Model\Serializer $serializer,
        DeleteTemporaryMatches $deleteTemporaryMatches
    ) {
        parent::__construct(
            $context,
            $resultRawFactory,
            $layoutFactory,
            $registry,
            $categoryRepository,
            $categoryFactory,
            $dataProvider,
            $resultJsonFactory
        );
        $this->ruleFactory = $ruleFactory;
        $this->serializer = $serializer;
        $this->deleteTemporaryMatches = $deleteTemporaryMatches;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $category = $this->initCategory();
        $this->dataProvider->setCategoryId((int)$category->getId());

        $storeId = $this->getRequest()->getParam('store', $this->getRequest()->getParam('store_id'));
        $this->dataProvider->setStoreId($storeId);

        $topProductData = $this->getRequest()->getParam('top_product_data', []);
        if (!empty($topProductData)) {
            $this->dataProvider->resortPositionData($topProductData['source_position'], 0);
            $this->dataProvider->setProductPositionData([$topProductData['entity_id'] => 0]);
        }

        $moveProductData = $this->getRequest()->getParam('move_product_data', []);
        if (!empty($moveProductData)) {
            $this->dataProvider->resortPositionData(
                $moveProductData['source_position'],
                $moveProductData['destination_position']
            );
            $this->dataProvider->setProductPositionData(
                [$moveProductData['entity_id'] => $moveProductData['destination_position']]
            );
        }

        $automaticProductData = $this->getRequest()->getParam('automatic_product_data', []);
        if (!empty($automaticProductData)) {
            $this->dataProvider->unsetProductPositionData($automaticProductData['entity_id']);
            $position = $this->dataProvider->getCurrentProductPosition($automaticProductData['entity_id']);
            $this->dataProvider->resortPositionData($automaticProductData['source_position'], $position);
        }

        $ruleData = $this->getRequest()->getParam('rule', []);
        if (isset($ruleData['conditions'])) {
            $rule = $this->ruleFactory->create();
            $rule->loadPost($ruleData);

            $serializedConditions = $this->serializer->serialize($rule->getConditions()->asArray());
            $this->dataProvider->setSerializedRuleConditions($serializedConditions);

            if ($this->getRequest()->getParam('force_reset')) {
                $this->deleteTemporaryMatches->execute($category, $serializedConditions);
            }
        }

        $positions = $this->getRequest()->getParam('positions', []);
        $sortOrder = $this->getRequest()->getParam('sort_order', false);

        $this->dataProvider->setProductPositionData($positions);
        $this->dataProvider->setSortOrder($sortOrder);

        $resultJson->setData([]);
        return $resultJson;
    }
}
