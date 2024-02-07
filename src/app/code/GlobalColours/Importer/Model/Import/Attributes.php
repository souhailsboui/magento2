<?php

namespace GlobalColours\Importer\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Class Courses
 */
class Attributes extends AbstractEntity
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    private $moduleDataSetup;

    const ENTITY_CODE = "attributes";
    const TABLE = "eav_attribute";
    const ENTITY_ID_COLUMN = "attribute_id";

    /**
     * If we should check column names
     */
    protected $needColumnCheck = true;

    /**
     * Need to log in import history
     */
    protected $logInHistory = true;

    /**
     * Permanent entity columns.
     */
    protected $_permanentAttributes = [
        // 'attribute_code'
    ];

    /**
     * Valid column names
     */
    protected $validColumnNames = [
        "attribute_id",
        "attribute_code",
        "attribute_label",
        "attribute_set_name",
        "attribute_type",
        "is_visual",
        "is_searchable",
        "is_filterable",
        "is_comparable",
        "is_visible_on_front",
        "is_html_allowed_on_front",
        "is_used_for_price_rules",
        "is_filterable_in_search",
        "used_in_product_listing",
        "used_for_sort_by",
        "is_visible_in_advanced_search",
        "is_used_for_promo_rules",
        "is_required_in_admin_store",
        "is_used_in_grid",
        "is_visible_in_grid",
        "is_filterable_in_grid"
    ];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Courses constructor.
     *
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        LoggerInterface $logger,
        ModuleDataSetupInterface $moduleDataSetup,

    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->initMessageTemplates();
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        return true;
        $code = $rowData['attribute_code'] ?? '';
        $attributeSetName = $rowData['attribute_set_name'] ?? '';
        $attributeType = $rowData['attribute_type'] ?? '';

        if (!$code) {
            $this->addRowError('AttributeCodeIsRequired', $rowNum);
        }

        if (!$attributeSetName) {
            $this->addRowError('AttributeSetNameIsRequired', $rowNum);
        }

        if ($attributeType && ($attributeType != "select" || $attributeType != "multiselect" || $attributeType != "text" || $attributeType != "float" || $attributeType != "weight")) {
            $this->addRowError('AttributeTypeIsNotValid', $rowNum);
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                // $this->deleteEntity();
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->saveAndReplaceEntity();
                break;
            case Import::BEHAVIOR_APPEND:
                $this->saveAndReplaceEntity();
                break;
        }

        return true;
    }

    /**
     * Delete entities
     *
     * @return bool
     */
    private function deleteEntity(): bool
    {
        return true;
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);

                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowId = $rowData[static::ENTITY_ID_COLUMN];
                    $rows[] = $rowId;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }

        if ($rows) {
            return $this->deleteEntityFinish(array_unique($rows));
        }

        return false;
    }


    private function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextUniqueBunch($this->getIds())) {
            $entityList = [];

            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);

                    continue;
                }

                $entityList[] = $row;
            }

            if (Import::BEHAVIOR_REPLACE === $behavior) {
                // if ($rows && $this->deleteEntityFinish(array_unique($rows))) {
                $this->saveEntityFinish($entityList);
                // }
            } elseif (Import::BEHAVIOR_APPEND === $behavior) {
                $this->saveEntityFinish($entityList);
            }
        }
    }

    private function saveEntityFinish(array $entityData): bool
    {
        if ($entityData) {
            $rows = [];

            foreach ($entityData as $entityRows) {
                $rows[] = $entityRows;
            }

            $this->moduleDataSetup->getConnection()->startSetup();
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $attribute_code = $row['attribute_code'];
                    $select = $this->moduleDataSetup->getConnection()->select()
                        ->from($this->moduleDataSetup->getTable('eav_attribute'), ['attribute_id'])
                        ->where('attribute_code = ?', $attribute_code);
                    $attributeId = $this->moduleDataSetup->getConnection()->fetchOne($select);

                    $select = $this->moduleDataSetup->getConnection()->select()
                        ->from($this->moduleDataSetup->getTable('eav_entity_type'), ['entity_type_id'])
                        ->where('entity_type_code = ?', "catalog_product");
                    $entityTypeId = $this->moduleDataSetup->getConnection()->fetchOne($select);

                    if (!$entityTypeId)
                        continue;

                    $select = $this->moduleDataSetup->getConnection()->select()
                        ->from($this->moduleDataSetup->getTable('eav_attribute_group'), ['attribute_group_id'])
                        ->where('attribute_group_code = ?', "product-details");
                    $attributeGroupId = $this->moduleDataSetup->getConnection()->fetchOne($select);

                    if (!$attributeGroupId)
                        continue;

                    $select = $this->moduleDataSetup->getConnection()->select()
                        ->from($this->moduleDataSetup->getTable('eav_attribute_set'), ['attribute_set_id'])
                        ->where('entity_type_id = ?', $entityTypeId)
                        ->where('attribute_set_name = ?', $row["attribute_set_name"]);
                    $attributeSetId = $this->moduleDataSetup->getConnection()->fetchOne($select);

                    if (!$attributeSetId)
                        continue;


                    $attribute = [
                        'frontend_label' => $row["attribute_label"],
                        'attribute_code' => $row["attribute_code"],
                        'is_user_defined' => 1,
                        'is_required' => 0,
                        'is_unique' => 0,
                        'entity_type_id' => $entityTypeId,
                        'frontend_input' => $this->getFrontEndInput($row["attribute_type"]),
                        'backend_type' => $this->getBackendEndType($row["attribute_type"]),
                        'backend_model' => $row["attribute_type"] && $row["attribute_type"] === "multiselect" ? "Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend" : null,
                        'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table'
                    ];

                    $entityAttribute = [
                        'attribute_id' => $attributeId,
                        'attribute_group_id' => $attributeGroupId,
                        'attribute_set_id' => $attributeSetId,
                        'entity_type_id' => $entityTypeId,
                    ];

                    $catalogAttribute = [
                        "attribute_id" => $attributeId,
                        "is_global" => 0,
                        "is_visible" => 1,
                        "is_searchable" => $row["is_searchable"] ? 1 : 0,
                        "is_filterable" => $row["is_filterable"] ? 1 : 0,
                        "is_comparable" => $row["is_comparable"] ? 1 : 0,
                        "is_visible_on_front" => $row["is_visible_on_front"] ? 1 : 0,
                        "is_html_allowed_on_front" => $row["is_html_allowed_on_front"] ? 1 : 0,
                        "is_used_for_price_rules" => $row["is_used_for_price_rules"] ? 1 : 0,
                        "is_filterable_in_search" => $row["is_filterable_in_search"] ? 1 : 0,
                        "used_in_product_listing" => $row["used_in_product_listing"] ? 1 : 0,
                        "used_for_sort_by" => $row["used_for_sort_by"]  ? 1 : 0,
                        "is_visible_in_advanced_search" => $row["is_visible_in_advanced_search"]  ? 1 : 0,
                        "is_used_for_promo_rules" => $row["is_used_for_promo_rules"]  ? 1 : 0,
                        "is_required_in_admin_store" => $row["is_required_in_admin_store"] ? 1 : 0,
                        "is_used_in_grid" => $row["is_used_in_grid"]  ? 1 : 0,
                        "is_visible_in_grid" => $row["is_visible_in_grid"]  ? 1 : 0,
                        "is_filterable_in_grid" => $row["is_filterable_in_grid"]  ? 1 : 0,
                        "is_global" => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                        "additional_data" => $row["is_visual"] ? '{"swatch_input_type":"visual","update_product_preview_image":"0","use_product_image_for_swatch":"0"}' : null
                    ];

                    if ($attributeId) {
                        $this->moduleDataSetup->getConnection()->update(
                            $this->moduleDataSetup->getTable('eav_attribute'),
                            $attribute,
                            ['attribute_id = ?' => $attributeId]
                        );

                        $this->moduleDataSetup->getConnection()->update(
                            $this->moduleDataSetup->getTable('eav_entity_attribute'),
                            $entityAttribute,
                            ['attribute_id = ?' => $attributeId]
                        );

                        $this->moduleDataSetup->getConnection()->update(
                            $this->moduleDataSetup->getTable('catalog_eav_attribute'),
                            $catalogAttribute,
                            ['attribute_id = ?' => $attributeId]
                        );

                        $this->countItemsUpdated += 1;
                    } else {
                        $this->moduleDataSetup->getConnection()->insert(
                            $this->moduleDataSetup->getTable('eav_attribute'),
                            $attribute
                        );

                        $select = $this->moduleDataSetup->getConnection()->select()
                            ->from($this->moduleDataSetup->getTable('eav_attribute'), ['attribute_id'])
                            ->where('attribute_code = ?', $attribute_code);
                        $attributeId = $this->moduleDataSetup->getConnection()->fetchOne($select);

                        $entityAttribute["attribute_id"] = $attributeId;

                        $this->moduleDataSetup->getConnection()->insert(
                            $this->moduleDataSetup->getTable('eav_entity_attribute'),
                            $entityAttribute
                        );

                        $catalogAttribute["attribute_id"] = $attributeId;

                        $this->moduleDataSetup->getConnection()->insert(
                            $this->moduleDataSetup->getTable('catalog_eav_attribute'),
                            $catalogAttribute
                        );

                        $this->countItemsCreated += 1;
                    }
                }
            }
            $this->moduleDataSetup->getConnection()->endSetup();
        }
        return true;
    }

    private function getFrontEndInput($attributeType)
    {
        switch ($attributeType) {
            case 'multiselect':
                return "multiselect";

            case 'select':
                return "select";

            case 'float':
                return "text";

            case 'weight':
                return "weight";

            case 'text':
                return "text";

            default:
                return "text";
        }
    }

    private  function getBackendEndType($attributeType)
    {
        switch ($attributeType) {
            case 'select':
                return "int";

            case 'float':
                return "decimal";

            case 'weight':
                return "decimal";

            default:
                return "text";
        }
    }


    /**
     * Delete entities
     *
     * @param array $entityIds
     *
     * @return bool
     */
    private function deleteEntityFinish(array $entityIds): bool
    {
        if ($entityIds) {
            try {
                $this->countItemsDeleted += $this->connection->delete(
                    $this->connection->getTableName(static::TABLE),
                    $this->connection->quoteInto(static::ENTITY_ID_COLUMN . ' IN (?)', $entityIds)
                );

                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Init Error Messages
     */
    private function initMessageTemplates()
    {
        $this->addMessageTemplate(
            'AttributeCodeIsRequired',
            __('The attribute code cannot be empty.')
        );
        $this->addMessageTemplate(
            'AttributeSetNameIsRequired',
            __('The attribute set name cannot be empty.')
        );
        $this->addMessageTemplate(
            'AttributeTypeIsNotValid',
            __('The attribute type is not valid.')
        );
    }
}
