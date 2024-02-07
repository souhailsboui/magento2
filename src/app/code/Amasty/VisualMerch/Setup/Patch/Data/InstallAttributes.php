<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallAttributes implements DataPatchInterface
{
    private const AMLANDING_IS_DYNAMIC = 'amlanding_is_dynamic';
    private const AMLANDING_DYNAMIC_CONDITIONS = 'amasty_dynamic_conditions';
    private const AMLANDING_CATEGORY_PRODUCT_SORT = 'amasty_category_product_sort';

    /**
     * @var EavSetup
     */
    private $eavSetup;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->eavSetup = $eavSetupFactory->create(['setup' => $setup]);
    }

    public function apply(): self
    {
        $this->addIsDynamicAttribute();
        $this->addDynamicConditionsAttribute();
        $this->addCategoryProductSortAttribute();

        return $this;
    }

    private function addIsDynamicAttribute(): void
    {
        if (!$this->eavSetup->getAttribute(Category::ENTITY, self::AMLANDING_IS_DYNAMIC)) {
            $this->eavSetup->addAttribute(
                Category::ENTITY,
                self::AMLANDING_IS_DYNAMIC,
                [
                    'type' => 'int',
                    'label' => 'Is dynamic category',
                    'visible' => true,
                    'default' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'note' => "Get products by dynamic rules",
                    'group' => 'General Information',
                    'sort_order' => 900,
                    'required' => false
                ]
            );
        } else {
            $this->eavSetup->updateAttribute(
                Category::ENTITY,
                self::AMLANDING_IS_DYNAMIC,
                'is_required',
                false
            );
        }

        $this->eavSetup->updateAttribute(
            Category::ENTITY,
            self::AMLANDING_IS_DYNAMIC,
            'frontend_input',
            null
        );
    }

    private function addCategoryProductSortAttribute(): void
    {
        if (!$this->eavSetup->getAttribute(Category::ENTITY, self::AMLANDING_CATEGORY_PRODUCT_SORT)) {
            $this->eavSetup->addAttribute(
                Category::ENTITY,
                self::AMLANDING_CATEGORY_PRODUCT_SORT,
                [
                    'type' => 'int',
                    'label' => 'Category Products Sort',
                    'visible' => false,
                    'is_user_defined' => true,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'General Information',
                    'sort_order' => 901,
                    'required' => false
                ]
            );
        } else {
            $this->eavSetup->updateAttribute(
                Category::ENTITY,
                self::AMLANDING_CATEGORY_PRODUCT_SORT,
                'is_required',
                false
            );
        }

        $this->eavSetup->updateAttribute(
            Category::ENTITY,
            self::AMLANDING_CATEGORY_PRODUCT_SORT,
            'frontend_input',
            null
        );
    }

    private function addDynamicConditionsAttribute(): void
    {
        if (!$this->eavSetup->getAttribute(Category::ENTITY, self::AMLANDING_DYNAMIC_CONDITIONS)) {
            $this->eavSetup->addAttribute(
                Category::ENTITY,
                self::AMLANDING_DYNAMIC_CONDITIONS,
                [
                    'type' => 'text',
                    'label' => 'Dynamic Products Conditions',
                    'visible' => false,
                    'is_user_defined' => true,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'General Information',
                    'sort_order' => 901,
                    'required' => false
                ]
            );
        } else {
            $this->eavSetup->updateAttribute(
                Category::ENTITY,
                self::AMLANDING_DYNAMIC_CONDITIONS,
                'is_required',
                false
            );
        }

        $this->eavSetup->updateAttribute(
            Category::ENTITY,
            self::AMLANDING_DYNAMIC_CONDITIONS,
            'frontend_input',
            null
        );
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
