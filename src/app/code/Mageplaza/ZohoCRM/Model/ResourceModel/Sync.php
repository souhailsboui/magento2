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

namespace Mageplaza\ZohoCRM\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Sync
 * @package Mageplaza\ZohoCRM\Model\ResourceModel
 */
class Sync extends AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_zoho_sync', 'sync_id');
    }

    /**
     * @param string $table
     *
     * @return array
     */
    public function getFieldTable($table)
    {
        $connection = $this->getConnection();

        $tableName = $this->getTable($table);
        $fields    = array_keys($connection->describeTable($tableName));
        $data      = [];
        foreach ($fields as $field) {
            $data[] = [
                'value' => '{{' . $field . '}}',
                'label' => ucfirst(implode(' ', explode('_', $field)))
            ];
        }

        return $data;
    }
}
