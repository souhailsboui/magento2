<?php

namespace MageMe\WebForms\Model\ResourceModel\Statistics;

use MageMe\WebForms\Model\ResourceModel\AbstractCollection;
use MageMe\WebForms\Model\ResourceModel\Statistics as StatisticsResource;
use MageMe\WebForms\Model\Statistics;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Statistics::class, StatisticsResource::class);
    }
}