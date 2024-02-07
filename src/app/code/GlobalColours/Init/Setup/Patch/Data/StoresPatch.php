<?php

namespace GlobalColours\Init\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

use Psr\Log\LoggerInterface;

class StoresPatch implements DataPatchInterface, PatchRevertableInterface
{
    private $moduleDataSetup;

    private $mainWebSiteCode = 'base';

    private $globalStoreGroupCode = 'global_store';
    private $audStoreCode = 'aud_store_view';
    private $usdStoreCode = 'usd_store_view';
    private $cadStoreCode = 'cad_store_view';
    private $gbpStoreCode = 'gbp_store_view';
    private $eurStoreCode = 'eur_store_view';
    private $nzdStoreCode = 'nzd_store_view';

    private $defaultWebSiteCode = 'base';
    private $defaultWebSiteName = 'Main Website';
    private $defaultStoreGroupCode = 'main_website_store';
    private $defaultStoreGroupName = 'Main Website Store';
    private $defaultStoreCode = 'default';
    private $defaultStoreName = 'Default Store View';


    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // Delete the default store
        $this->deleteStoreByCode($this->defaultStoreCode);

        // Delete the default store group
        $this->deleteStoreGroupByCode($this->defaultStoreGroupCode);

        // Create Main website
        $webSiteId = $this->createWebsiteIfNotFound($this->mainWebSiteCode, 'Main Website', 1);

        // Create Global store group
        $globalStoreGroupId = $this->createStoreGroup($webSiteId, $this->globalStoreGroupCode, 'Global Store', 2);

        // Make Global store group the default
        $this->updateWebsiteDefaultStoreGroup($webSiteId, $globalStoreGroupId);

        // Create the global stores
        $audStoreId = $this->createStore($webSiteId, $globalStoreGroupId, $this->audStoreCode, 'AUD', 'AUD', 'AUD', 'AU', 'AU,NZ,US,CA,GB,EU', 'en_UK', 'kgs', false);
        $usdStoreId = $this->createStore($webSiteId, $globalStoreGroupId, $this->usdStoreCode, 'USD', 'USD', 'USD', 'US', 'AU,NZ,US,CA,GB,EU', 'en_US', 'kgs', false);
        $cadStoreId = $this->createStore($webSiteId, $globalStoreGroupId, $this->cadStoreCode, 'CAD', 'CAD', 'CAD', 'CA', 'AU,NZ,US,CA,GB,EU', 'en_US', 'kgs', false);
        $gbpStoreId = $this->createStore($webSiteId, $globalStoreGroupId, $this->gbpStoreCode, 'GBP', 'GBP', 'GBP', 'GB', 'AU,NZ,US,CA,GB,EU', 'en_UK', 'kgs', false);
        $eurStoreId = $this->createStore($webSiteId, $globalStoreGroupId, $this->eurStoreCode, 'EUR', 'EUR', 'EUR', 'EU', 'AU,NZ,US,CA,GB,EU', 'en_US', 'kgs', false);
        $nzdStoreId = $this->createStore($webSiteId, $globalStoreGroupId, $this->nzdStoreCode, 'NZD', 'NZD', 'NZD', 'NZ', 'AU,NZ,US,CA,GB,EU', 'en_US', 'kgs', false);

        // Make aud the default store for global
        $this->updateStoreGroupDefaultStore($globalStoreGroupId, $audStoreId);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // Delete the global stores
        $this->deleteStoreByCode($this->audStoreCode);
        $this->deleteStoreByCode($this->usdStoreCode);
        $this->deleteStoreByCode($this->cadStoreCode);
        $this->deleteStoreByCode($this->gbpStoreCode);
        $this->deleteStoreByCode($this->eurStoreCode);
        $this->deleteStoreByCode($this->nzdStoreCode);

        // Delete the global store group
        $this->deleteStoreGroupByCode($this->globalStoreGroupCode);

        // Restore default website
        $defaultWebSiteId = $this->createWebsiteIfNotFound($this->defaultWebSiteCode, $this->defaultWebSiteName, 1);

        // Restore default store group
        $defaultStoreGroupId = $this->createStoreGroup($defaultWebSiteId, $this->defaultStoreGroupCode, $this->defaultStoreGroupName, 2);

        // Restore default store
        $defaultStoreId = $this->createStore($defaultWebSiteId, $defaultStoreGroupId, $this->defaultStoreCode, $this->defaultStoreName);

        // Update default store default group
        $this->updateWebsiteDefaultStoreGroup($defaultWebSiteId, $defaultStoreGroupId);

        $this->updateStoreGroupDefaultStore($defaultStoreGroupId, $defaultStoreId);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    private function deleteWebsiteByCode($webSiteCode)
    {
        // Delete the website
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('store_website'),
            ['code = ?' => $webSiteCode]
        );
    }

    private function deleteStoreGroupByCode($storeCode)
    {
        // Delete the store group
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('store_group'),
            ['code = ?' => $storeCode]
        );
    }

    private function deleteStoreByCode($storeCode)
    {
        // Fetch the store's ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('store'), ['store_id'])
            ->where('code = ?', $storeCode);
        $storeId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        $this->resetConfigForStore($storeId);

        $this->deleteStoreRelatedTables($storeId);

        // Delete the store
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('store'),
            ['code = ?' => $storeCode]
        );
    }

    private function createWebsiteIfNotFound($webSiteCode, $webSiteName, $isDefault = 0)
    {
        // Remove default from all other websites
        if ($isDefault === 1) {
            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable('store_website'),
                ['is_default' => 0],
                ['is_default = ?' => 1]
            );
        }

        // Fetch the created website's ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('store_website'), ['website_id'])
            ->where('code = ?', $webSiteCode);
        $oldWebSiteId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        // Create website if not found else update
        $webSite = [
            'code' => $webSiteCode,
            'name' => $webSiteName,
            'sort_order' => 0,
            'default_group_id' => 0,
            'is_default' => $isDefault
        ];
        if ($oldWebSiteId === null) {
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('store_website'),
                $webSite
            );

            // Fetch the created website's ID
            $select = $this->moduleDataSetup->getConnection()->select()
                ->from($this->moduleDataSetup->getTable('store_website'), ['website_id'])
                ->where('code = ?', $webSiteCode);
            $webSiteId = $this->moduleDataSetup->getConnection()->fetchOne($select);

            return $webSiteId;
        } else {
            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable('store_website'),
                $webSite,
                ['website_id = ?' => $oldWebSiteId]
            );
            return $oldWebSiteId;
        }
    }

    private function updateWebsiteDefaultStoreGroup($webSiteId, $storeGroupId)
    {
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('store_website'),
            ['default_group_id' => $storeGroupId],
            ['website_id = ?' => $webSiteId]
        );
    }

    private function updateStoreGroupDefaultStore($storeGroupId, $storeId)
    {
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('store_group'),
            ['default_store_id' => $storeId],
            ['group_id = ?' => $storeGroupId]
        );
    }

    private function createStoreGroup($webSiteId, $storeGroupCode, $storeGroupName, $rootCategroy = 0)
    {
        // Create store group
        $storeGroup = [
            'website_id' => $webSiteId,
            'code' => $storeGroupCode,
            'name' => $storeGroupName,
            'root_category_id' => $rootCategroy
        ];
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('store_group'),
            $storeGroup
        );

        // Fetch the created group's ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('store_group'), ['group_id'])
            ->where('code = ?', $storeGroupCode);
        $groupId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        return $groupId;
    }

    private function createStore($webSiteId, $storeGroupId, $storeCode, $storeName, $currencyCode = null, $allowedCurrencies = 'USD', $countryId = null, $allowedCountries = 'US', $localeCode = null, $weightUnit = 'kgs', $disableStoreCredit = false)
    {
        // Create store
        $store = [
            'code' => $storeCode,
            'website_id' => $webSiteId,
            'group_id' => $storeGroupId,
            'name' => $storeName,
            'sort_order' => 0,
            'is_active' => 1,
        ];
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('store'),
            $store
        );

        // Fetch the created store's ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('store'), ['store_id'])
            ->where('code = ?', $store['code']);
        $storeId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        if ($currencyCode !== null) {
            $this->insertCurrencyConfigForStore($storeId, $currencyCode, $allowedCurrencies);
        }
        if ($countryId !== null) {
            $this->insertCountryConfigForStore($storeId, $countryId, $allowedCountries);
        }
        if ($localeCode !== null) {
            $this->insertlocaleConfigForStore($storeId, $localeCode, $weightUnit);
        }
        if ($disableStoreCredit === true) {
            $this->disableStoreCredit($storeId);
        }

        $this->createStoreRelatedTables($storeId);

        return $storeId;
    }

    private function disableStoreCredit($storeId)
    {
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'mpstorecredit/general/enabled',
                'value' => 0,
                'scope' => 'stores',
                'scope_id' => $storeId,
            ]
        );
    }

    private function resetConfigForStore($storeId)
    {
        // Reset currency configuration settings for a store
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'scope = ?' => 'stores',
                'scope_id = ?' => $storeId,
            ]
        );
    }

    private function insertCurrencyConfigForStore($storeId, $currencyCode, $allowedCurrencies)
    {
        // insert currency configuration settings for a store
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'currency/options/default',
                'value' => $currencyCode,
                'scope' => 'stores',
                'scope_id' => $storeId,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'currency/options/allow',
                'value' => $allowedCurrencies,
                'scope' => 'stores',
                'scope_id' => $storeId,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'currency/options/base',
                'value' =>  'AUD',
                'scope' => 'stores',
                'scope_id' => $storeId,
            ]
        );
    }

    private function insertCountryConfigForStore($storeId, $countryId, $allowedCountries)
    {
        // insert country configuration settings for a store
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'general/country/default',
                'value' => $countryId,
                'scope' => 'stores',
                'scope_id' => $storeId,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'general/country/allow',
                'value' => $allowedCountries,
                'scope' => 'stores',
                'scope_id' => $storeId,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'general/country/destinations',
                'value' => $allowedCountries,
                'scope' => 'stores',
                'scope_id' => $storeId,
            ]
        );
    }
    private function insertLocaleConfigForStore($storeId, $localeCode, $weightUnit)
    {
        // insert locale configuration settings for a store
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'general/locale/code',
                'value' => $localeCode,
                'scope' => 'stores',
                'scope_id' => $storeId,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'general/locale/weight_unit',
                'value' => $weightUnit,
                'scope' => 'stores',
                'scope_id' => $storeId,
            ]
        );
    }

    private function createStoreRelatedTables($storeId)
    {
        // Order sequnce
        $sequenceOrderTable = $this->moduleDataSetup->getConnection()->newTable(
            $this->moduleDataSetup->getTable('sequence_order_' . $storeId)
        )->addColumn(
            'sequence_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Sequence Value'
        );

        $this->moduleDataSetup->getConnection()->createTable($sequenceOrderTable);

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('sales_sequence_meta'),
            [
                'entity_type' => 'order',
                'store_id' => $storeId,
                'sequence_table' => 'sequence_order_' . $storeId,
            ]
        );

        // Fetch the created meta's ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('sales_sequence_meta'), ['meta_id'])
            ->where('sequence_table = ?', 'sequence_order_' . $storeId);
        $metaId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        // Create profile
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('sales_sequence_profile'),
            [
                'meta_id' => $metaId,
                'prefix' => $storeId,
                'max_value' => 4294967295,
                'warning_value' => 4294967295,
                'is_active' => 1
            ]
        );



        // Invoice sequnce
        $sequenceInvoiceTable = $this->moduleDataSetup->getConnection()->newTable(
            $this->moduleDataSetup->getTable('sequence_invoice_' . $storeId)
        )->addColumn(
            'sequence_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Sequence Value'
        );

        $this->moduleDataSetup->getConnection()->createTable($sequenceInvoiceTable);

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('sales_sequence_meta'),
            [
                'entity_type' => 'invoice',
                'store_id' => $storeId,
                'sequence_table' => 'sequence_invoice_' . $storeId,
            ]
        );

        // Fetch the created meta's ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('sales_sequence_meta'), ['meta_id'])
            ->where('sequence_table = ?', 'sequence_invoice_' . $storeId);
        $metaId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        // Create profile
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('sales_sequence_profile'),
            [
                'meta_id' => $metaId,
                'prefix' => $storeId,
                'max_value' => 4294967295,
                'warning_value' => 4294967295,
                'is_active' => 1
            ]
        );

        // Credit memo sequence
        $sequenceCreditmemoTable = $this->moduleDataSetup->getConnection()->newTable(
            $this->moduleDataSetup->getTable('sequence_creditmemo_' . $storeId)
        )->addColumn(
            'sequence_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Sequence Value'
        );

        $this->moduleDataSetup->getConnection()->createTable($sequenceCreditmemoTable);

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('sales_sequence_meta'),
            [
                'entity_type' => 'creditmemo',
                'store_id' => $storeId,
                'sequence_table' => 'sequence_creditmemo_' . $storeId,
            ]
        );

        // Fetch the created meta's ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('sales_sequence_meta'), ['meta_id'])
            ->where('sequence_table = ?', 'sequence_creditmemo_' . $storeId);
        $metaId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        // Create profile
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('sales_sequence_profile'),
            [
                'meta_id' => $metaId,
                'prefix' => $storeId,
                'max_value' => 4294967295,
                'warning_value' => 4294967295,
                'is_active' => 1
            ]
        );


        // Shipment sequence
        $sequenceShipmentTable = $this->moduleDataSetup->getConnection()->newTable(
            $this->moduleDataSetup->getTable('sequence_shipment_' . $storeId)
        )->addColumn(
            'sequence_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Sequence Value'
        );

        $this->moduleDataSetup->getConnection()->createTable($sequenceShipmentTable);

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('sales_sequence_meta'),
            [
                'entity_type' => 'shipment',
                'store_id' => $storeId,
                'sequence_table' => 'sequence_shipment_' . $storeId,
            ]
        );

        // Fetch the created meta's ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('sales_sequence_meta'), ['meta_id'])
            ->where('sequence_table = ?', 'sequence_shipment_' . $storeId);
        $metaId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        // Create profile
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('sales_sequence_profile'),
            [
                'meta_id' => $metaId,
                'prefix' => $storeId,
                'max_value' => 4294967295,
                'warning_value' => 4294967295,
                'is_active' => 1
            ]
        );
    }

    private function deleteStoreRelatedTables($storeId)
    {
        $this->moduleDataSetup->getConnection()->dropTable($this->moduleDataSetup->getTable('sequence_order_' . $storeId));
        $this->moduleDataSetup->getConnection()->dropTable($this->moduleDataSetup->getTable('sequence_invoice_' . $storeId));
        $this->moduleDataSetup->getConnection()->dropTable($this->moduleDataSetup->getTable('sequence_creditmemo_' . $storeId));
        $this->moduleDataSetup->getConnection()->dropTable($this->moduleDataSetup->getTable('sequence_shipment_' . $storeId));

        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('sales_sequence_meta'),
            [
                'store_id = ?' => $storeId,
            ]
        );
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}
