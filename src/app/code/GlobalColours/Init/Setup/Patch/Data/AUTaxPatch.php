<?php

namespace GlobalColours\Init\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Psr\Log\LoggerInterface;

class AUTaxPatch implements DataPatchInterface, PatchRevertableInterface
{
    private $moduleDataSetup;

    private $taxRuleCode = 'GST';
    private $taxRateCode = 'GST';
    private $countryId = 'AU';
    private $taxPercentage = 10.0000;


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

        // Create a new tax rule
        $taxRule = [
            'code' => $this->taxRuleCode,
            'priority' => 0,
        ];
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('tax_calculation_rule'),
            $taxRule
        );

        // Fetch the created tax rule ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('tax_calculation_rule'), ['tax_calculation_rule_id'])
            ->where('code = ?', $taxRule['code']);
        $taxRuleId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        // Create a new tax rate for each country
        $taxRate = [
            'tax_country_id' => $this->countryId,
            'tax_region_id' => 0,
            'tax_postcode' => '*',
            'rate' => $this->taxPercentage,
            'code' => $this->taxRateCode,
        ];
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('tax_calculation_rate'),
            $taxRate
        );

        // Fetch the created tax rate ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('tax_calculation_rate'), ['tax_calculation_rate_id'])
            ->where('code = ?', $taxRate['code']);
        $taxRateId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        // Create association between tax rate and tax rule
        $association = [
            'tax_calculation_rule_id' => $taxRuleId,
            'tax_calculation_rate_id' => $taxRateId,
        ];

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('tax_calculation'),
            $association
        );


        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // Fetch the created tax rule ID
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($this->moduleDataSetup->getTable('tax_calculation_rule'), ['tax_calculation_rule_id'])
            ->where('code = ?', $this->taxRuleCode);
        $taxRuleId = $this->moduleDataSetup->getConnection()->fetchOne($select);

        // Delete the created connections
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('tax_calculation'),
            ['tax_calculation_rule_id = ?' => $taxRuleId]
        );

        // Delete the created tax rule
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('tax_calculation_rule'),
            ['tax_calculation_rule_id = ?' => $taxRuleId]
        );

        // Delete created tax rate for each country

        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('tax_calculation_rate'),
            ['code = ?' => $this->taxRateCode]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
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
