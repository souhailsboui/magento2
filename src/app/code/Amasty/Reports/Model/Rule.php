<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model;

use Amasty\Reports\Api\Data\RuleInterface;
use Amasty\Reports\Model\Indexer\Rule\MatchingProducts;
use Amasty\Reports\Model\ResourceModel\Rule as RuleResource;
use Amasty\Reports\Model\OptionSource\Rule\Status;
use Magento\Framework\App\ObjectManager;

class Rule extends \Magento\CatalogRule\Model\Rule implements RuleInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_reports_rule';

    /**
     * @var int|array|null
     */
    private $productsFilter = null;

    /**
     * @var \Amasty\Reports\Model\Indexer\Rule\RuleProcessor
     */
    private $ruleProcessor;

    /**
     * @var \Amasty\Reports\Model\Indexer\Rule\ProductProcessor
     */
    private $productProcessor;

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->ruleProcessor = $this->getData('rule_processor');
        $this->productProcessor = $this->getData('product_processor');
        if ($this->getData('amasty_serializer')) {
            $this->serializer = $this->getData('amasty_serializer');
        }
        $this->_init(RuleResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(RuleInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(RuleInterface::ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(RuleInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(RuleInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->_getData(RuleInterface::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(RuleInterface::UPDATED_AT, $updatedAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSerializedConditions()
    {
        return $this->_getData(RuleInterface::CONDITIONS);
    }

    /**
     * @inheritdoc
     */
    public function setSerializedConditions($conditions)
    {
        $this->setData(RuleInterface::CONDITIONS, $conditions);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData(RuleInterface::TITLE);
    }

    /**
     * @param string $title
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function setTitle($title)
    {
        $this->setData(RuleInterface::TITLE, $title);

        return $this;
    }

    /**
     * @return int
     */
    public function getPin()
    {
        return $this->_getData(RuleInterface::PIN);
    }

    /**
     * @param int $pin
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function setPin($pin)
    {
        $this->setData(RuleInterface::PIN, $pin);

        return $this;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getConditions()) {
            $this->setConditionsSerialized($this->serializer->serialize($this->getConditions()->asArray()));
            $this->_conditions = null;
        }
        if ($this->productProcessor->isIndexerScheduled()) {
            $this->setStatus(Status::PROCESSING);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isConditionEmpty()
    {
        $conditions = $this->getConditions()->asArray();

        return !isset($conditions['conditions']);
    }

    /**
     * @param  int|array|null $productIds
     *
     * @return Rule
     */
    public function setProductsFilter($productIds)
    {
        $this->productsFilter = $productIds;

        return $this;
    }

    /**
     * @return array|int|null
     */
    public function getProductsFilter()
    {
        return $this->productsFilter;
    }

    /**
     * @return array|null
     * @deprecated moved to separated class
     * @see MatchingProducts
     */
    public function getMatchingProductIdsByReportRule()
    {
        return ObjectManager::getInstance()->get(MatchingProducts::class)
            ->resolveProductIdsByReportRule($this);
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        $this->getResource()->addCommitCallback([$this, 'reindex']);

        return $this;
    }

    /**
     * @return void
     */
    public function reindex()
    {
        $this->ruleProcessor->reindexRow($this->getId());
    }
}
