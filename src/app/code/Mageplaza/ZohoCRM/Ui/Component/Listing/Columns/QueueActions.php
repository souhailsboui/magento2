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

use Exception;
use Magento\Ui\Component\Listing\Columns\Column;
use Mageplaza\ZohoCRM\Model\Source\QueueStatus;

/**
 * Class QueueActions
 * @package Mageplaza\ZohoCRM\Ui\Component\Listing\Columns
 */
class QueueActions extends Column
{
    /**
     * @param array $dataSource
     *
     * @return array
     * @throws Exception
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'label' => __('View')
                ];

                $item['popup_content'] = $this->getPopupContent($item);
            }
        }

        return $dataSource;
    }

    /**
     * @param array $item
     *
     * @return string
     * @throws Exception
     */
    public function getPopupContent($item)
    {
        $json = json_encode(
            json_decode($item['json_response']),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $html = '<div id="mpzoho-popup"><table class="data-table admin__table-secondary"><tbody>';
        $html .= $this->createRow(__('Queue ID'), $item['queue_id']);
        $html .= $this->createRow(__('Object'), $item['object']);
        $html .= $this->createRow(__('Status'), QueueStatus::getOptionArray()[$item['status']]);
        $html .= $this->createRow(__('Sync Rule'), $item['sync_id']);
        $html .= $this->createRow(__('Website'), $item['website']);
        $html .= $this->createRow(__('Magento Object'), $item['magento_object']);
        $html .= $this->createRow(__('Zoho Module'), $item['zoho_module']);
        $html .= $this->createRow(__('Json Response'), '<pre>' . $json . '</pre>');
        $html .= '</tbody></table></div>';

        return $html;
    }

    /**
     * @param string $label
     * @param string $value
     *
     * @return string
     */
    public function createRow($label, $value)
    {
        return '<tr><th>' . $label . '</th><td>' . $value . '</td></tr>';
    }
}
