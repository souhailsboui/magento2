<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model\ResourceModel\Report\Order;

class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection
{
    /**
     * Add period filter by created_at attribute
     *
     * @param string|null $period
     *
     * @return \Amasty\Reports\Model\ResourceModel\Report\Order\Collection
     *
     * @throws \Exception
     */
    public function addCreateAtFilter($period = null)
    {
        $dateEnd = new \DateTime();
        $dateStart = new \DateTime();

        $dateEnd->setTime(23, 59, 59);
        $dateStart->setTime(0, 0, 0);

        $this->addFieldToFilter(
            'created_at',
            [
                'from' => $dateStart->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                'to' => $dateEnd->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ]
        );

        return $this;
    }
}
