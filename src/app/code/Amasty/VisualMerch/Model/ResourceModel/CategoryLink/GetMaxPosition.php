<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\ResourceModel\CategoryLink;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Zend_Db_Expr;

class GetMaxPosition
{
    /**
     * @var CategoryResourceModel
     */
    private $categoryResourceModel;

    public function __construct(CategoryResourceModel $categoryResourceModel)
    {
        $this->categoryResourceModel = $categoryResourceModel;
    }

    /**
     * Retrieve max product position for category.
     *
     * @param int $categoryId
     * @return int
     */
    public function execute(int $categoryId): int
    {
        $connection = $this->categoryResourceModel->getConnection();
        $select = $connection->select()->from(
            $this->categoryResourceModel->getCategoryProductTable(),
            ['position' => new Zend_Db_Expr('MAX(position)')]
        )->where('category_id = ?', $categoryId);

        return (int) $connection->fetchOne($select);
    }
}
