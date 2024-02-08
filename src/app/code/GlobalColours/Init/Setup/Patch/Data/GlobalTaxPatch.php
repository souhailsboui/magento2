<?php

namespace GlobalColours\Init\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Psr\Log\LoggerInterface;

class GlobalTaxPatch implements DataPatchInterface, PatchRevertableInterface
{
    private $moduleDataSetup;

    private $taxRuleCode = 'non-AU-Zero-Tax';

    private $countries = [
        'AD',
        'AE',
        'AF',
        'AG',
        'AI',
        'AL',
        'AM',
        'AN',
        'AO',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AW',
        'AX',
        'AZ',
        'BA',
        'BB',
        'BD',
        'BE',
        'BF',
        'BG',
        'BH',
        'BI',
        'BJ',
        'BL',
        'BM',
        'BN',
        'BO',
        'BQ',
        'BR',
        'BS',
        'BT',
        'BV',
        'BW',
        'BY',
        'BZ',
        'CA',
        'CC',
        'CD',
        'CF',
        'CG',
        'CH',
        'CI',
        'CK',
        'CL',
        'CM',
        'CN',
        'CO',
        'CR',
        'CU',
        'CV',
        'CW',
        'CX',
        'CY',
        'CZ',
        'DE',
        'DJ',
        'DK',
        'DM',
        'DO',
        'DZ',
        'EC',
        'EE',
        'EG',
        'EH',
        'ER',
        'ES',
        'ET',
        'FI',
        'FJ',
        'FK',
        'FM',
        'FO',
        'FR',
        'GA',
        'GB',
        'GD',
        'GE',
        'GF',
        'GG',
        'GH',
        'GI',
        'GL',
        'GM',
        'GN',
        'GP',
        'GQ',
        'GR',
        'GS',
        'GT',
        'GU',
        'GW',
        'GY',
        'HK',
        'HM',
        'HN',
        'HR',
        'HT',
        'HU',
        'ID',
        'IE',
        'IL',
        'IM',
        'IN',
        'IO',
        'IQ',
        'IR',
        'IS',
        'IT',
        'JE',
        'JM',
        'JO',
        'JP',
        'KE',
        'KG',
        'KH',
        'KI',
        'KM',
        'KN',
        'KP',
        'KR',
        'KW',
        'KY',
        'KZ',
        'LA',
        'LB',
        'LC',
        'LI',
        'LK',
        'LR',
        'LS',
        'LT',
        'LU',
        'LV',
        'LY',
        'MA',
        'MC',
        'MD',
        'ME',
        'MF',
        'MG',
        'MH',
        'MK',
        'ML',
        'MM',
        'MN',
        'MO',
        'MP',
        'MQ',
        'MR',
        'MS',
        'MT',
        'MU',
        'MV',
        'MW',
        'MX',
        'MY',
        'MZ',
        'NA',
        'NC',
        'NE',
        'NF',
        'NG',
        'NI',
        'NL',
        'NO',
        'NP',
        'NR',
        'NU',
        'NZ',
        'OM',
        'PA',
        'PE',
        'PF',
        'PG',
        'PH',
        'PK',
        'PL',
        'PM',
        'PN',
        'PS',
        'PT',
        'PW',
        'PY',
        'QA',
        'RE',
        'RO',
        'RS',
        'RU',
        'RW',
        'SA',
        'SB',
        'SC',
        'SD',
        'SE',
        'SG',
        'SH',
        'SI',
        'SJ',
        'SK',
        'SL',
        'SM',
        'SN',
        'SO',
        'SR',
        'ST',
        'SV',
        'SX',
        'SY',
        'SZ',
        'TC',
        'TD',
        'TF',
        'TG',
        'TH',
        'TJ',
        'TK',
        'TL',
        'TM',
        'TN',
        'TO',
        'TR',
        'TT',
        'TV',
        'TW',
        'TZ',
        'UA',
        'UG',
        'UM',
        'US',
        'UY',
        'UZ',
        'VA',
        'VC',
        'VE',
        'VG',
        'VI',
        'VN',
        'VU',
        'WF',
        'WS',
        'XK',
        'YE',
        'YT',
        'ZA',
        'ZM',
        'ZW'
    ];

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
        foreach ($this->countries as $country) {
            $taxRate = [
                'tax_country_id' => $country,
                'tax_region_id' => 0,
                'tax_postcode' => '*',
                'rate' => 0.0000,
                'code' => $country . '-*-*-Zero rate',
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
        }

        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path LIKE ?' => 'tax/%',
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
            ]
        );


        // Config custom global tax values (include tax, tax calculations ...etc)
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/classes/shipping_tax_class',
                'value' => '2',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/classes/default_product_tax_class',
                'value' => '2',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/classes/default_customer_tax_class',
                'value' => '3',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/calculation/algorithm',
                'value' => 'TOTAL_BASE_CALCULATION',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/calculation/based_on',
                'value' => 'shipping',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/calculation/price_includes_tax',
                'value' => '1',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/calculation/shipping_includes_tax',
                'value' => '1',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/calculation/apply_after_discount',
                'value' => '1',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/calculation/discount_tax',
                'value' => '1',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/notification/ignore_discount',
                'value' => '0',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/notification/ignore_price_display',
                'value' => '0',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/notification/ignore_apply_discount',
                'value' => '0',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/calculation/apply_tax_on',
                'value' => '0',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/calculation/cross_border_trade_enabled',
                'value' => '0',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/defaults/country',
                'value' => 'AU',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/defaults/postcode',
                'value' => '*',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/cart_display/price',
                'value' => '2',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/cart_display/subtotal',
                'value' => '2',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/cart_display/shipping',
                'value' => '2',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/cart_display/full_summary',
                'value' => '1',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/sales_display/price',
                'value' => '2',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/sales_display/subtotal',
                'value' => '2',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/sales_display/shipping',
                'value' => '2',
                'scope' => 'default',
                'scope_id' => 0,
            ]
        );
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => 'tax/sales_display/full_summary',
                'value' => '1',
                'scope' => 'default',
                'scope_id' => 0,
            ]
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
        foreach ($this->countries as $country) {
            $this->moduleDataSetup->getConnection()->delete(
                $this->moduleDataSetup->getTable('tax_calculation_rate'),
                ['code = ?' => $country . '-*-*-Zero rate']
            );
        }

        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path LIKE ?' => 'tax/%',
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
            ]
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
