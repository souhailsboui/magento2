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
declare(strict_types=1);

namespace Mageplaza\ZohoCRM\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\UrlInterface;
use Mageplaza\ZohoCRM\Model\QueueFactory;
use Mageplaza\ZohoCRM\Model\SyncFactory;

/**
 * Class UpdateQueue
 * @package Mageplaza\ZohoCRM\Setup\Patch\Data
 */
class UpdateQueue implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var SyncFactory
     */
    protected $syncFactory;

    /**
     * UpdateQueue constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param UrlInterface $urlBuilder
     * @param QueueFactory $queueFactory
     * @param SyncFactory $syncFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        UrlInterface $urlBuilder,
        QueueFactory $queueFactory,
        SyncFactory $syncFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->urlBuilder      = $urlBuilder;
        $this->queueFactory    = $queueFactory;
        $this->syncFactory     = $syncFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $queueCollection = $this->queueFactory->create()->getCollection();
        if ($queueCollection->getSize()) {
            foreach ($queueCollection as &$queue) {
                $item = [
                    'magento_object' => $queue->getMagentoObject(),
                    'object'         => $queue->getObject()
                ];
                $queueModel  = $this->queueFactory->create();
                $queueObject = $queueModel->getQueueObject($item, $this->urlBuilder);
                $syncName    = $this->syncFactory->create()->load($queue->getSyncId())->getName();

                $queue->setSyncName($syncName);
                $queue->setObjectName($queueObject['name']);
            }

            $queueCollection->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
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
        return '1.0.1';
    }
}
