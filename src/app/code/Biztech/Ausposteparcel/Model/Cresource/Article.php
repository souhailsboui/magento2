<?php

namespace Biztech\Ausposteparcel\Model\Cresource;

class Article extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        // Note that the label_id refers to the key field in your database table.
        $this->_init('biztech_ausposteParcel_article', 'article_id');
    }
}
