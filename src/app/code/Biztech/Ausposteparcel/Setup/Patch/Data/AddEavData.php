<?php

namespace Biztech\Ausposteparcel\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute as ProductAttribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddEavData implements DataPatchInterface, PatchRevertableInterface {
	/** @var ModuleDataSetupInterface */
	private $moduleDataSetup;

	/** @var EavSetupFactory */
	private $eavSetupFactory;
	private $attributeInfo;

	/**
	 * @param ModuleDataSetupInterface $moduleDataSetup
	 * @param EavSetupFactory $eavSetupFactory
	 */
	public function __construct(
		ModuleDataSetupInterface $moduleDataSetup,
		EavSetupFactory $eavSetupFactory,
		ProductAttribute $attributeInfo
	) {
		$this->moduleDataSetup = $moduleDataSetup;
		$this->eavSetupFactory = $eavSetupFactory;
		$this->attributeInfo = $attributeInfo;
	}

	/**
	 * {@inheritdoc}
	 */
	public function apply() {

		/** @var EavSetup $eavSetup */
		$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

		if (!$this->attributeInfo->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'height')) {
			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'height',
				[
					'type' => 'int',
					'backend' => '',
					'frontend' => '',
					'label' => 'Height',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'group' => 'General',
					'global' => 1,
					'visible' => true,
					'required' => false,
					'user_defined' => false,
					'default' => null,
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'is_used_in_grid' => true,
					'is_visible_in_grid' => true,
					'is_filterable_in_grid' => true,
					'is_searchable_in_grid' => true,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => 'simple',
				]
			);
		}

		if (!$this->attributeInfo->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'width')) {
			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'width',
				[
					'type' => 'int',
					'backend' => '',
					'frontend' => '',
					'label' => 'Width',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'group' => 'General',
					'global' => 1,
					'visible' => true,
					'required' => false,
					'user_defined' => false,
					'default' => null,
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'is_used_in_grid' => true,
					'is_visible_in_grid' => true,
					'is_filterable_in_grid' => true,
					'is_searchable_in_grid' => true,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => 'simple',
				]
			);
		}

		if (!$this->attributeInfo->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'length')) {
			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'length',
				[
					'type' => 'int',
					'backend' => '',
					'frontend' => '',
					'label' => 'Length',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'group' => 'General',
					'global' => 1,
					'visible' => true,
					'required' => false,
					'user_defined' => false,
					'default' => null,
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'is_used_in_grid' => true,
					'is_visible_in_grid' => true,
					'is_filterable_in_grid' => true,
					'is_searchable_in_grid' => true,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => 'simple',
				]
			);
		}

		//install states for australia
		$data = [
			['AU', 'ACT', 'Australia Capital Territory'],
			['AU', 'NSW', 'New South Wales'],
			['AU', 'NT', 'Northern Territory'],
			['AU', 'QLD', 'Queensland'],
			['AU', 'SA', 'South Australia'],
			['AU', 'TAS', 'Tasmania'],
			['AU', 'VIC', 'Victoria'],
			['AU', 'WA', 'Western Australia'],
		];
		foreach ($data as $row) {
			$dbConnection = $this->moduleDataSetup->getConnection();
			$select = $dbConnection->select()
				->from($this->moduleDataSetup->getTable('directory_country_region'))
				->where('country_id ="' . $row[0] . '" AND code =  "' . $row[1] . '"');
			$result = $dbConnection->fetchOne($select);
			if (!$result) {
				$bind = ['country_id' => $row[0], 'code' => $row[1], 'default_name' => $row[2]];
				$this->moduleDataSetup->getConnection()->insert($this->moduleDataSetup->getTable('directory_country_region'), $bind);
				$regionId = $this->moduleDataSetup->getConnection()->lastInsertId($this->moduleDataSetup->getTable('directory_country_region'));

				$bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $row[2]];
				$this->moduleDataSetup->getConnection()->insert($this->moduleDataSetup->getTable('directory_country_region_name'), $bind);
			}
		}
	}

	/**
	 * @return ResourceConnection
	 */
	public function getResource() {
		return $this->_resource;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getDependencies() {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function revert() {
		$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
		$eavSetup->removeAttribute(
			\Magento\Customer\Model\Product::ENTITY,
			'translated'
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAliases() {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getVersion() {
		return '1.1.2';
	}
}
