<?php

namespace Machship\Fusedship\Block\Product\View;

use Magento\Catalog\Block\Product\AbstractProduct;

class QuickQuote extends AbstractProduct
{

	public function getCurrentProduct()
	{
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $currentProduct  = $objectManager->get('Magento\Framework\Registry')->registry('current_product');

        return $currentProduct;
	}

    public function getChildProducts() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');

        $productTypeInstance = $product->getTypeInstance();
        $usedProducts = $productTypeInstance->getUsedProducts($product);

        return $usedProducts;
    }

    public function getCurrentCurrency()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currency = $currencysymbol->getStore()->getCurrentCurrency();

        return $currency;
    }

    public function useFusedshipRates($product_id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // Fusedship Product Cartons
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

        $deployment_config = $objectManager->get('Magento\Framework\App\DeploymentConfig');

        $table_prefix = $deployment_config->get('db/table_prefix');

        $connection = $resource->getConnection();

        $fusedshipProductDataTable  =   $connection->getTableName('fusedship_product_data');

        if (!empty($table_prefix) && strpos($fusedshipProductDataTable, $table_prefix) === false) {
            $fusedshipProductDataTable = $table_prefix . $fusedshipProductDataTable;
        }

        $select = $connection->select()->from($fusedshipProductDataTable)->where('product_id = :product_id');

        $binds = ['product_id' => (int) $product_id];

        $fusedship_product_data_arr = $connection->fetchAll($select, $binds);

        $use_fusedship_rates = $fusedship_product_data_arr[0]['use_fusedship_rates'] ?? 0;

        return $use_fusedship_rates == 1;
    }

    public function getLookupFieldTitle() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->getLookupFieldTitle();
    }

    public function getLookupFieldPlaceholder() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->getLookupFieldPlaceholder();
    }

    public function isProductWidgetEnabled() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->isProductWidgetEnabled();
    }

    public function productWidgetTitle() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->productWidgetTitle();
    }

    public function productWidgetDescription() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->productWidgetDescription();
    }

    public function productWidgetLookupFieldTitle() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->productWidgetLookupFieldTitle();
    }

    public function productWidgetLookupFieldPlaceholder() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->productWidgetLookupFieldPlaceholder();
    }

    public function popupButtonLabel() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->popupButtonLabel();
    }

    public function rateMessage() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->rateMessage();
    }

    public function rateTriggerCharacter() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->rateTriggerCharacter();
    }

    public function displayInPopup() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->displayProductWidgetInPopup();
    }

    public function isShowResidentialOption() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->getIsShowResidentialOption();
    }

    public function getDefaultResBusOption() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->getDefaultResBusOption();
    }


}
