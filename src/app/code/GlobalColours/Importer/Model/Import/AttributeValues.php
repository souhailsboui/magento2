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
class AttributeValues extends AbstractEntity
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    private $moduleDataSetup;

    const ENTITY_CODE = "attribute_values";
    const TABLE = "eav_attribute_option";
    const ENTITY_ID_COLUMN = "option_id";

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
        "option_id", "attribute_code", "attribute_value", "attribute_swatch"
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
        $value = $rowData['attribute_value'] ?? '';

        if (!$code) {
            $this->addRowError('AttributeCodeIsRequired', $rowNum);
        }

        if (!$value) {
            $this->addRowError('ValueIsRequired', $rowNum);
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
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
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
                        ->from($this->moduleDataSetup->getTable('eav_attribute'), ['attribute_id', 'frontend_input'])
                        ->where('attribute_code = ?', $attribute_code);
                    $attribute = $this->moduleDataSetup->getConnection()->fetchRow($select);

                    if (!$attribute || ($attribute['frontend_input'] !== 'select' && $attribute['frontend_input'] !== 'multiselect')) {
                        continue;
                    }

                    $attribute_value = $row['attribute_value'];
                    $attribute_swatch = $row['attribute_swatch'];

                    $skipSwatch = false;
                    $skipValue = false;

                    if ($attribute_value) {
                        $select = $this->moduleDataSetup->getConnection()->select()
                            ->from(
                                ['eav_option_value' => $this->moduleDataSetup->getTable('eav_attribute_option_value')],
                                ['value_id']
                            )
                            ->join(
                                ['eav_option' => $this->moduleDataSetup->getTable('eav_attribute_option')],
                                'eav_option_value.option_id = eav_option.option_id',
                                []
                            )
                            ->join(
                                ['eav_attribute' => $this->moduleDataSetup->getTable('eav_attribute')],
                                'eav_option.attribute_id = eav_attribute.attribute_id',
                                []
                            )
                            ->where('eav_attribute.attribute_code = ?', $attribute_code)
                            ->where('eav_option_value.value = ?', $attribute_value);
                        $value = $this->moduleDataSetup->getConnection()->fetchOne($select);

                        if ($value) {
                            $skipValue = true;
                        }
                    } else {
                        $skipValue = true;
                    }

                    if ($attribute_swatch) {
                        // $select = $this->moduleDataSetup->getConnection()->select()
                        //     ->from(
                        //         ['eav_option_swatch' => $this->moduleDataSetup->getTable('eav_attribute_option_swatch')],
                        //         ['swatch_id']
                        //     )
                        //     ->join(
                        //         ['eav_option' => $this->moduleDataSetup->getTable('eav_attribute_option')],
                        //         'eav_option_swatch.option_id = eav_option.option_id',
                        //         []
                        //     )
                        //     ->join(
                        //         ['eav_attribute' => $this->moduleDataSetup->getTable('eav_attribute')],
                        //         'eav_option.attribute_id = eav_attribute.attribute_id',
                        //         []
                        //     )
                        //     ->where('eav_attribute.attribute_code = ?', $attribute_code)
                        //     ->where('eav_option_swatch.value = ?', $attribute_swatch);
                        // $swatch = $this->moduleDataSetup->getConnection()->fetchOne($select);

                        // if ($swatch) {
                        //     $skipSwatch = true;
                        // }
                    } else {
                        $skipSwatch = true;
                    }

                    if ($skipValue) {
                        continue;
                    }

                    $attribute_option = [
                        'attribute_id' => $attribute['attribute_id'],
                        'sort_order' => 0,
                    ];
                    $this->moduleDataSetup->getConnection()->insert(
                        $this->moduleDataSetup->getTable('eav_attribute_option'),
                        $attribute_option
                    );

                    // Get the last inserted option ID
                    $lastOptionId = $this->moduleDataSetup->getConnection()->fetchOne(
                        $this->resource->getConnection()
                            ->select()
                            ->from('eav_attribute_option', ['option_id'])
                            ->order('option_id DESC')
                    );

                    if (!$skipValue) {
                        $attribute_option_value = [
                            'option_id' => $lastOptionId,
                            'store_id' => 0,
                            'value' => $attribute_value
                        ];
                        $this->moduleDataSetup->getConnection()->insert(
                            $this->moduleDataSetup->getTable('eav_attribute_option_value'),
                            $attribute_option_value
                        );
                    }

                    if (!$skipSwatch) {
                        $attribute_option_swatch = [
                            'option_id' => $lastOptionId,
                            'store_id' => 0,
                            'type' => 1,
                            'value' => $attribute_swatch
                        ];
                        $this->moduleDataSetup->getConnection()->insert(
                            $this->moduleDataSetup->getTable('eav_attribute_option_swatch'),
                            $attribute_option_swatch
                        );
                    }

                    $this->countItemsCreated += 1;
                }
            }
            $this->moduleDataSetup->getConnection()->endSetup();
        }
        return true;
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
            'ValueIsRequired',
            __('Attribute value cannot be empty.')
        );
    }
}
