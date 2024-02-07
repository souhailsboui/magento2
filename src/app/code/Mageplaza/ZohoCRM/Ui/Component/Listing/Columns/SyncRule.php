<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mageplaza\ZohoCRM\Model\SyncFactory;

/**
 * Class SyncRule
 * @package Mageplaza\ZohoCRM\Ui\Component\Listing\Columns
 */
class SyncRule extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var SyncFactory
     */
    protected $syncFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param SyncFactory $syncFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        SyncFactory $syncFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder  = $urlBuilder;
        $this->syncFactory = $syncFactory;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $syncName                     = $this->syncFactory->create()->load($item['sync_id'])->getName();
                $url                          = $this->urlBuilder->getUrl(
                    'mpzoho/sync/edit',
                    ['id' => $item['sync_id']]
                );
                $item[$this->getData('name')] = '<a href="' . $url . '" target="_blank">' . $syncName . '</a>';
            }
        }

        return $dataSource;
    }

    /**
     * @return $this|void
     */
    protected function applySorting()
    {
        $isSortable = $this->getData('config/sortable');
        $sorting    = $this->getContext()->getRequestParam('sorting');
        if ($isSortable !== false
            && !empty($sorting['field'])
            && !empty($sorting['direction'])
            && $sorting['field'] === 'sync_id'
        ) {
            $this->getContext()->getDataProvider()->addOrder(
                'sync_name',
                strtoupper($sorting['direction'])
            );
        } else {
            parent::applySorting(); // TODO: Change the autogenerated stub
        }

        return $this;
    }
}