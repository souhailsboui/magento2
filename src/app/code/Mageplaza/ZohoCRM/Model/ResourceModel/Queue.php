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

namespace Mageplaza\ZohoCRM\Model\ResourceModel;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\UrlInterface;
use Mageplaza\ZohoCRM\Model\QueueFactory;
use Mageplaza\ZohoCRM\Model\Source\QueueStatus;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;
use Mageplaza\ZohoCRM\Model\SyncFactory;

/**
 * Class Queue
 * @package Mageplaza\ZohoCRM\Model\ResourceModel
 */
class Queue extends AbstractDb
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var SyncFactory
     */
    protected $syncFactory;

    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        QueueFactory $queueFactory,
        SyncFactory $syncFactory,
        $connectionName = null
    ) {
        $this->urlBuilder   = $urlBuilder;
        $this->queueFactory = $queueFactory;
        $this->syncFactory  = $syncFactory;

        parent::__construct(
            $context,
            $connectionName
        );
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_zoho_queue', 'queue_id');
    }

    /**
     * @param array $data
     *
     * @throws Exception
     */
    public function insertQueues($data)
    {
        $this->getConnection()->beginTransaction();
        try {
            $tableName = $this->getMainTable();
            $this->getConnection()->insertMultiple($tableName, $data);
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @param array $queues
     *
     * @return $this
     */
    public function updateQueues($queues)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $connection->insertOnDuplicate(
                $this->getTable('mageplaza_zoho_queue'),
                $queues,
                ['queue_id', 'status', 'json_response', 'total_sync']
            );

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
        }

        return $this;
    }

    /**
     * @param array $zohoEntity
     *
     * @return $this
     */
    public function updateZohoEntity($zohoEntity)
    {
        foreach ($zohoEntity as $zohoModule => $updateData) {
            $fieldName     = 'entity_id';
            $zohoFieldName = 'zoho_entity';
            $tableName     = '';
            switch ($zohoModule) {
                case ZohoModule::PRODUCT:
                    $tableName = 'catalog_product_entity';
                    break;
                case ZohoModule::ACCOUNT:
                    $tableName = 'customer_entity';
                    break;
                case ZohoModule::CONTACT:
                    $tableName     = 'customer_entity';
                    $zohoFieldName = 'zoho_contact_entity';

                    break;
                case ZohoModule::LEAD:
                    $tableName     = 'customer_entity';
                    $zohoFieldName = 'zoho_lead_entity';

                    break;
                case ZohoModule::CAMPAIGN:
                    $tableName = 'catalogrule';
                    $fieldName = 'rule_id';

                    break;
                case ZohoModule::ORDER:
                    $tableName = 'sales_order';

                    break;
                case ZohoModule::INVOICE:
                    $tableName = 'sales_invoice';
                    break;
            }

            if (!$tableName) {
                return $this;
            }

            $connection = $this->getConnection();
            $value      = $connection->getCaseSql($fieldName, $updateData, $zohoFieldName);
            $where      = [$fieldName . ' IN (?)' => array_keys($updateData)];
            try {
                $connection->beginTransaction();
                $connection->update($this->getTable($tableName), [$zohoFieldName => $value], $where);
                $connection->commit();
            } catch (Exception $e) {
                $connection->rollBack();
            }
        }

        return $this;
    }

    /**
     * @param string $days
     *
     * @throws LocalizedException
     */
    public function deleteRecordAfter($days)
    {
        if ($days) {
            $connection = $this->getConnection();
            $table      = $this->getMainTable();
            $statusSql  = 'status = ' . QueueStatus::SUCCESS;
            $connection->delete(
                $table,
                [
                    $statusSql,
                    'created_at < NOW() - INTERVAL ' . $days . ' DAY'
                ]
            );
        }
    }

    /**
     * @param AbstractModel $object
     *
     * @return AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $queueModel  = $this->queueFactory->create();
        $item = [
            'magento_object' => $object->getMagentoObject(),
            'object'         => $object->getObject()
        ];
        $queueObject = $queueModel->getQueueObject($item, $this->urlBuilder);
        $syncName    = $this->syncFactory->create()->load($object->getSyncId())->getName();

        $object->setSyncName($syncName);
        $object->setObjectName($queueObject['name']);

        return parent::_beforeSave($object); // TODO: Change the autogenerated stub
    }
}
