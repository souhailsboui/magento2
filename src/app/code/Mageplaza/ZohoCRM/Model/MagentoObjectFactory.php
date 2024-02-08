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
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;
use UnexpectedValueException;

/**
 * Class MagentoObjectFactory
 * @package Mageplaza\ZohoCRM\Model
 */
class MagentoObjectFactory
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $map;

    /**
     * MagentoObjectFactory constructor.
     *
     * @param ObjectManager $objectManager
     * @param array $map
     */
    public function __construct(
        ObjectManager $objectManager,
        array $map = []
    ) {
        $this->objectManager = $objectManager;
        $this->map           = $map;
    }

    /**
     * @param $param
     * @param array $arguments
     *
     * @return mixed
     */
    public function create($param, array $arguments = [])
    {
        if (!isset($this->map[$param])) {
            throw new UnexpectedValueException(
                __('Magento object does not exist')
            );
        }

        return $this->objectManager->create($this->map[$param], $arguments);
    }

    /**
     * @param string $type
     * @param Sync $sync
     * @param array $arguments
     *
     * @return mixed
     */
    public function getCollection($type, Sync $sync, array $arguments = [])
    {
        $objectCollection = $this->create($type, $arguments)->getCollection();
        $zohoFieldName    = 'zoho_entity';
        if ($sync->getZohoModule() === ZohoModule::LEAD) {
            $zohoFieldName = 'zoho_lead_entity';
        } elseif ($sync->getZohoModule() === ZohoModule::CONTACT) {
            $zohoFieldName = 'zoho_contact_entity';
        }

        $objectCollection->addFieldToFilter($zohoFieldName, [['eq' => ''], ['null' => true]]);

        return $objectCollection;
    }
}
