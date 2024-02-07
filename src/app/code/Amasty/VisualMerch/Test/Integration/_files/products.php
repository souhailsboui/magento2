<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var $product1 \Magento\Catalog\Model\Product */
$product1 = Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product1
    ->setTypeId('simple')
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('merch-simple-1')
    ->setPrice(40)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('merch-simple-1')
    ->save();

/** @var $product2 \Magento\Catalog\Model\Product */
$product2 = Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product2
    ->setTypeId('simple')
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('merch-simple-2')
    ->setPrice(50)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('merch-simple-2')
    ->save();

/** @var $product3 \Magento\Catalog\Model\Product */
$product3 = Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product3
    ->setTypeId('simple')
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('merch-simple-3')
    ->setPrice(55)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('merch-simple-3')
    ->save();

/** @var $product4 \Magento\Catalog\Model\Product */
$product4 = Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product4
    ->setTypeId('simple')
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('merch-simple-4')
    ->setPrice(60)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('merch-simple-4')
    ->save();
