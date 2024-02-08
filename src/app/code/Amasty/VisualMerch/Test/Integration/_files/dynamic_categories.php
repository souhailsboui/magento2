<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\CatalogRule\Api\Data\RuleInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var CategoryInterfaceFactory $categoryFactory */
$categoryFactory = $objectManager->get(CategoryInterfaceFactory::class);
/** @var DefaultCategory $categoryHelper */
$categoryHelper = $objectManager->get(DefaultCategory::class);

$dynamicCatregoryCondition1 = <<<CONDITION
{"type":"Amasty\\\\VisualMerch\\\\Model\\\\Rule\\\\Condition\\\\Combine","attribute":null,"operator":null,"value":"1",
"is_value_processed":null,"aggregator":"all","conditions":
[{"type":"Amasty\\\\VisualMerch\\\\Model\\\\Rule\\\\Condition\\\\Price\\\\FinalPrice","attribute":"",
"operator":">","value":"40",
"is_value_processed":false}]}
CONDITION;

$category1 = $categoryFactory->create();
$category1->isObjectNew(true);
$category1Id = 9391;
$category1->setId($category1Id)
    ->setName('Dynamic Category 1')
    ->setParentId($categoryHelper->getId())
    ->setIsActive(true)
    ->setPosition(1)
    ->setPath('1/' . $categoryHelper->getId() . '/' . $category1Id)
    ->setData('amlanding_is_dynamic', 1)
    ->setData('amasty_dynamic_conditions', $dynamicCatregoryCondition1)
    ->save();

$dynamicCatregoryCondition2 = <<<CONDITION
{"type":"Amasty\\\\VisualMerch\\\\Model\\\\Rule\\\\Condition\\\\Combine","attribute":null,"operator":null,"value":"1",
"is_value_processed":null,"aggregator":"all","conditions":
[{"type":"Amasty\\\\VisualMerch\\\\Model\\\\Rule\\\\Condition\\\\Price\\\\FinalPrice","attribute":"",
"operator":"<=","value":"50",
"is_value_processed":false}]}
CONDITION;

$category2 = $categoryFactory->create();
$category2->isObjectNew(true);
$category2Id = 9392;
$category2->setId($category2Id)
    ->setName('Dynamic Category 2')
    ->setParentId($categoryHelper->getId())
    ->setIsActive(true)
    ->setPosition(1)
    ->setPath('1/' . $categoryHelper->getId() . '/' . $category2Id)
    ->setData('amlanding_is_dynamic', 1)
    ->setData('amasty_dynamic_conditions', $dynamicCatregoryCondition2)
    ->save();
