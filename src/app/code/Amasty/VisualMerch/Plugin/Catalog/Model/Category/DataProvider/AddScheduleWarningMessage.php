<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Catalog\Model\Category\DataProvider;

use Amasty\VisualMerch\Model\DynamicCategory\Index\IsCategoryWaitUpdate;
use Magento\Catalog\Model\Category\DataProvider;
use Magento\Framework\Stdlib\ArrayManager;

class AddScheduleWarningMessage
{
    /**
     * @var IsCategoryWaitUpdate
     */
    private $isCategoryWaitUpdate;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(IsCategoryWaitUpdate $isCategoryWaitUpdate, ArrayManager $arrayManager)
    {
        $this->isCategoryWaitUpdate = $isCategoryWaitUpdate;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @see DataProvider::getMeta
     */
    public function afterGetMeta(DataProvider $subject, array $meta): array
    {
        $category = $subject->getCurrentCategory();
        if ($category && $this->isCategoryWaitUpdate->execute((int)$category->getId())) {
            $meta = $this->arrayManager->set(
                'products_fieldset/children/amasty_merch_category_update_message/arguments/data/config',
                $meta,
                [
                    'componentType' => 'container',
                    'component' => 'Magento_Ui/js/form/components/html',
                    'content' => 'Please note: the category is not indexed therefore incorrect products may be '
                        . 'displayed on the products preview',
                    'sortOrder' => 5,
                    'additionalClasses' => 'ammerch-products-update-message message message-info',
                    'imports' => [
                        'visible' => 'index = amlanding_is_dynamic:checked',
                        '__disableTmpl' => ['visible' => false]
                    ]
                ]
            );
        }

        return $meta;
    }
}
