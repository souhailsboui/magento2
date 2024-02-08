<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\ResourceModel\CategoryLink;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;

class SaveLinks
{
    /**
     * @var CategoryResourceModel
     */
    private $categoryResourceModel;

    public function __construct(CategoryResourceModel $categoryResourceModel)
    {
        $this->categoryResourceModel = $categoryResourceModel;
    }

    public function execute(array $insertLinks): void
    {
        $this->categoryResourceModel->getConnection()->insertOnDuplicate(
            $this->categoryResourceModel->getCategoryProductTable(),
            $insertLinks,
            ['position']
        );
    }
}
