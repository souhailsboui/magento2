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

namespace Mageplaza\ZohoCRM\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\ZohoCRM\Model\Queue;
use Mageplaza\ZohoCRM\Model\ResourceModel\Queue as ResourceQueue;

/**
 * Class Collection
 * @package Mageplaza\ZohoCRM\Model\ResourceModel\Queue
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'queue_id';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(Queue::class, ResourceQueue::class);
    }

    /**
     * @param string $syncId
     * @param int|array $status
     *
     * @return int
     */
    public function getTotalRequest($syncId, $status = 0)
    {
        $this->addFieldToFilter('sync_id', $syncId);
        if ($status) {
            if (is_array($status)) {
                $this->addFieldToFilter(
                    ['status'],
                    [
                        ['eq' => $status[0]],
                        ['eq' => $status[1]]
                    ]
                );
            } else {
                $this->addFieldToFilter('status', $status);
            }
        }

        return $this->getSize();
    }
}
