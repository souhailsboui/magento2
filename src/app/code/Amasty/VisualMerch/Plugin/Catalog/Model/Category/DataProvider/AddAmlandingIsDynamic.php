<?php

declare(strict_types = 1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Catalog\Model\Category\DataProvider;

use Magento\Catalog\Model\Category\DataProvider;

class AddAmlandingIsDynamic
{
    /**
     * @see DataProvider::getMeta()
     *
     * @param DataProvider $subject
     * @param array $meta
     * @return array
     */
    public function afterGetMeta(DataProvider $subject, array $meta): array
    {
        $meta['general']['children']['amlanding_is_dynamic']['arguments']['data']['config'] = [
            'dataType' => 'boolean',
            'formElement' => 'checkbox',
            'visible' => true,
            'required' => true,
            'label' => __('Automatic Category'),
            'sortOrder' => '997',
            'notice' => __('Get products by dynamic rules'),
            'default' => '0',
            'size' => null,
            'validation' => ['required-entry' => true],
            'scopeLabel' => '[GLOBAL]',
            'componentType' => 'field',
            'disabled' => false,
            'source' => 'category',
            'prefer' => 'toggle',
            'valueMap' => ['true' => '1', 'false' => '0'],
            'tooltip' => [
                'description' => __(
                    'This option allows collecting and displaying in the chosen category'
                    . ' only those products that match the specified conditions. First, configure and apply the'
                    . ' conditions, then open the "Products Merchandising" tab to arrange the products.'
                )
            ]
        ];

        return $meta;
    }
}
