<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

declare(strict_types=1);

namespace Mageplaza\AutoRelated\Setup\Patch\Data;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule\CollectionFactory;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 * Class AddRuleTokenAttribute
 * @package Mageplaza\AutoRelated\Setup\Patch\Data
 */
class AddRuleTokenAttribute implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var Rule
     */
    private $ruleResource;

    /**
     * @var CollectionFactory
     */
    private $ruleCollection;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SalesSetupFactory $salesSetupFactory
     * @param Rule $ruleResource
     * @param CollectionFactory $ruleCollection
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $salesSetupFactory,
        Rule $ruleResource,
        CollectionFactory $ruleCollection
    ) {
        $this->moduleDataSetup   = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->ruleResource      = $ruleResource;
        $this->ruleCollection    = $ruleCollection;
    }

    /**
     * Do Upgrade
     *
     * @return void
     * @throws LocalizedException
     */
    public function apply()
    {
        /** @var \Mageplaza\AutoRelated\Model\Rule $rule */
        foreach ($this->ruleCollection->create()->getItems() as $rule) {
            if (!$rule->getToken()) {
                $rule->setToken($this->ruleResource->createToken());
                $this->ruleResource->save($rule);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $salesInstaller = $this->salesSetupFactory->create(
            [
                'resourceName' => 'sales_setup',
                'setup'        => $this->moduleDataSetup
            ]
        );
        $salesInstaller->removeAttribute('order_item', 'arp_rule_token');
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.2.1';
    }
}
