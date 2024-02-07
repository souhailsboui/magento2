<?php

namespace MageMe\WebForms\Plugin\Magento\Reports\Model\ResourceModel\Refresh\Collection;

use Exception;
use Magento\Framework\DataObject;
use Magento\Reports\Model\ResourceModel\Refresh\Collection;

class LoadData extends \Magento\Framework\Data\Collection
{
    /**
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterLoadData(Collection $collection, Collection $result, bool $printQuery = false, bool $logQuery = false): Collection
    {
        if (!count($this->_items)) {
            $data = [
                [
                    'id' => 'webform_statistics',
                    'report' => __('Webforms Statistics'),
                    'comment' => __('Webforms Statistics'),
                    'updated_at' => ''
                ],
            ];
            foreach ($data as $value) {
                $item = new DataObject();
                $item->setData($value);
                $this->addItem($item);
                $result->addItem($item);
            }
        }
        return $result;
    }

}