<?php

namespace MageMe\WebForms\Model\ResourceModel;

use MageMe\WebForms\Api\Data\StatisticsInterface;
use MageMe\WebForms\Setup\Table\StatisticsTable;

class Statistics extends AbstractDb
{
    const DB_TABLE = StatisticsTable::TABLE_NAME;
    const ID_FIELD = StatisticsInterface::ID;
}