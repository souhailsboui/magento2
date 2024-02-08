<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Ui\Component\Field\Listing;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Ui\Component\Common\Listing\AbstractStoreDataProvider;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\Listing;

class DataProvider extends AbstractStoreDataProvider
{
    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;

    /**
     * @inheritdoc
     */
    protected $columnsName = 'field_columns';

    /**
     * @inheritdoc
     */
    protected $storeFields = [
        FieldInterface::NAME
    ];

    /**
     * DataProvider constructor.
     *
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        FieldsetRepositoryInterface $fieldsetRepository,
        string                      $name,
        string                      $primaryFieldName,
        string                      $requestFieldName,
        ReportingInterface          $reporting,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        RequestInterface            $request,
        FilterBuilder               $filterBuilder,
        array                       $meta = [],
        array                       $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request,
            $filterBuilder, $meta, $data);
        $this->fieldsetRepository = $fieldsetRepository;
    }

    /**
     * @inheritdoc
     */
    public function getMeta(): array
    {
        $meta   = parent::getMeta();
        $formId = (int)$this->request->getParam(FieldsetInterface::FORM_ID);
        if (!$formId) {
            return $meta;
        }
        $meta[$this->columnsName]['children'][FieldInterface::FIELDSET_ID] = $this->getFieldsetColumnMeta($formId);
        return $meta;
    }

    /**
     * Get fieldset column meta
     *
     * @param int $formId
     * @return array
     */
    protected function getFieldsetColumnMeta(int $formId): array
    {
        $options = $this->getFieldsetsOptionsArray($formId);
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType' => 'select',
                        'component' => 'Magento_Ui/js/grid/columns/select',
                        'componentType' => Listing\Columns\Column::NAME,
                        'label' => __('Fieldset'),
                        'filter' => 'select',
                        'sortOrder' => 50,
                        'editor' => [
                            'editorType' => 'select',
                        ],
                    ],
                    'options' => $options,
                ]
            ],
        ];
    }

    /**
     * Get filtered fieldsets as options
     *
     * @param int $formId
     * @return array
     */
    public function getFieldsetsOptionsArray(int $formId): array
    {
        $options = [
            [
                'label' => '...',
                'value' => null
            ]
        ];
        if ($formId) {

            /** @var FieldsetInterface[] $fieldsets */
            $fieldsets = $this->fieldsetRepository->getListByWebformId($formId, $this->getScope())->getItems();
            foreach ($fieldsets as $fieldset) {
                $options[] = [
                    'label' => $fieldset->getName(),
                    'value' => $fieldset->getId(),
                ];
            }
        }
        return $options;
    }
}
