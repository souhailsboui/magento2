<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Rule\Condition\Stock;

use Amasty\VisualMerch\Model\Rule\Condition\AbstractCondition;
use Magento\Backend\Helper\Data as BackendData;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
use Magento\Framework\DB\Select;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Rule\Model\Condition\Context;

abstract class AbstractStock extends AbstractCondition
{
    /**
     * @var StockStatus
     */
    private $stockStatus;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        Context $context,
        BackendData $backendData,
        EavConfig $config,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ProductResource $productResource,
        AttributeSetCollection $attrSetCollection,
        LocaleFormat $localeFormat,
        StockStatus $stockStatus,
        ModuleManager $moduleManager,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );
        $this->stockStatus = $stockStatus;
        $this->moduleManager = $moduleManager;
    }

    abstract protected function getColumnExpression(Select $select): string;

    /**
     * @param ProductCollection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        $select = $productCollection->getSelect();

        if (!$this->isStockStatusJoined($select)) {
            $this->stockStatus->addStockStatusToSelect($select, $this->getStoreManager()->getWebsite());
            $this->updateJoinCondition($select);
        }

        $this->prepareCondition($select);
        $select->distinct(true);

        return $this;
    }

    private function prepareCondition(Select $select): void
    {
        $value = $this->getValue();
        $operator = $this->getOperatorForValidate();

        $this->_condition = $this->getOperatorCondition($this->getColumnExpression($select), $operator, $value);
    }

    private function isStockStatusJoined(Select $select): bool
    {
        $fromTables = $select->getPart(Select::FROM);
        return isset($fromTables[$this->_getAlias()]);
    }

    private function updateJoinCondition(Select $select): void
    {
        $fromTables = $select->getPart(Select::FROM);
        if ($fromTables[$this->_getAlias()]['tableName'] === $this->stockStatus->getMainTable()) {
            $joinCondition = $fromTables[$this->_getAlias()]['joinCondition'];
            $joinCondition = preg_replace(
                '@(stock_status.website_id=)\d+@',
                '$1 0',
                $joinCondition
            );
            $joinCondition .= ' AND stock_status.stock_id=1';
            $fromTables[$this->_getAlias()]['joinCondition'] = $joinCondition;

            $select->setPart(Select::FROM, $fromTables);
        }
    }

    protected function isMsiEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }

    protected function getCatalogInventoryTable(): string
    {
        return $this->stockStatus->getMainTable();
    }

    /**
     * @return string
     */
    protected function _getAlias()
    {
        return 'stock_status';
    }
}
