<?php

namespace Biztech\Ausposteparcel\Model\Cresource\Auspostlabel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /*public function __construct(
    \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Framework\DB\Adapter\AdapterInterface $connection = null, \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
                $entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource
        );
    }*/

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            'Biztech\Ausposteparcel\Model\Auspostlabel',
            'Biztech\Ausposteparcel\Model\Cresource\Auspostlabel'
        );
    }
}
