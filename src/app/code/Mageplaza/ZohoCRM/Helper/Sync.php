<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\ZohoCRM\Helper;

use DateTime;
use Exception;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\ClassModelFactory as TaxFactory;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\ZohoCRM\Model\Queue;
use Mageplaza\ZohoCRM\Model\QueueFactory;
use Mageplaza\ZohoCRM\Model\ResourceModel\Queue\Collection as QueueCollection;
use Mageplaza\ZohoCRM\Model\Source\MagentoObject;
use Mageplaza\ZohoCRM\Model\Source\QueueActions;
use Mageplaza\ZohoCRM\Model\Source\QueueStatus;
use Mageplaza\ZohoCRM\Model\Source\Status;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;
use Mageplaza\ZohoCRM\Model\SyncFactory;
use Laminas\Http\Request;

/**
 * Class Sync
 * @package Mageplaza\ZohoCRM\Helper
 */
class Sync extends AbstractData
{
    /**
     * Match all option in {{ }}
     */
    const PATTERN_OPTIONS = '/{{([a-zA-Z_]{0,50})(.*?)}}/si';

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var SyncFactory
     */
    protected $syncFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Mapping
     */
    protected $helperMapping;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var TaxFactory
     */
    protected $taxFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var array
     */
    protected $catalogRules = [];

    /**
     * @var array
     */
    protected $customerRepository = [];

    /**
     * @var array
     */
    protected $orderRepository = [];

    /**
     * @var array
     */
    protected $invoiceRepository = [];

    /**
     * @var array
     */
    protected $taxRepository = [];

    /**
     * @var int
     */
    protected $limitObjectSend = 0;

    /**
     * @var int
     */
    protected $countSyncSuccess = 0;

    /**
     * Sync constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param QueueFactory $queueFactory
     * @param SyncFactory $syncFactory
     * @param ProductFactory $productFactory
     * @param OrderFactory $orderFactory
     * @param InvoiceFactory $invoiceFactory
     * @param Data $helperData
     * @param Mapping $helperMapping
     * @param CategoryRepository $categoryRepository
     * @param TaxFactory $taxFactory
     * @param RuleFactory $ruleFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        QueueFactory $queueFactory,
        SyncFactory $syncFactory,
        ProductFactory $productFactory,
        OrderFactory $orderFactory,
        InvoiceFactory $invoiceFactory,
        Data $helperData,
        Mapping $helperMapping,
        CategoryRepository $categoryRepository,
        TaxFactory $taxFactory,
        RuleFactory $ruleFactory,
        CustomerFactory $customerFactory
    ) {
        $this->queueFactory       = $queueFactory;
        $this->syncFactory        = $syncFactory;
        $this->productFactory     = $productFactory;
        $this->helperData         = $helperData;
        $this->helperMapping      = $helperMapping;
        $this->categoryRepository = $categoryRepository;
        $this->taxFactory         = $taxFactory;
        $this->ruleFactory        = $ruleFactory;
        $this->customerFactory    = $customerFactory;
        $this->invoiceFactory     = $invoiceFactory;
        $this->orderFactory       = $orderFactory;
        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param Queue $queue
     *
     * @return DataObject|mixed
     */
    public function getObjectModel($queue)
    {
        $object = new DataObject();
        $id     = $queue->getObject();

        switch ($queue->getMagentoObject()) {
            case MagentoObject::PRODUCT:
                $object = $this->productFactory->create()->load($id);
                break;
            case MagentoObject::CATALOG_RULE:
                $object = $this->getCatalogRuleById($id);
                break;
            case MagentoObject::CUSTOMER:
                $object = $this->getCustomerById($id);
                break;
            case MagentoObject::ORDER:
                $object = $this->getOrderById($id);
                break;
            case MagentoObject::INVOICE:
                $object = $this->getInvoiceById($id);
                break;
        }

        return $object;
    }

    /**
     * @param string $type
     *
     * @return string string
     * @throws LocalizedException
     */
    public function getUrl($type)
    {
        $url = '';
        switch ($type) {
            case ZohoModule::PRODUCT:
                $url = $this->helperData->getAPIProductURL();
                break;
            case ZohoModule::CAMPAIGN:
                $url = $this->helperData->getAPICampaignURL();
                break;
            case ZohoModule::ACCOUNT:
                $url = $this->helperData->getAPIAccountURL();
                break;
            case ZohoModule::LEAD:
                $url = $this->helperData->getAPILeadURL();
                break;
            case ZohoModule::CONTACT:
                $url = $this->helperData->getAPIContactURL();
                break;
            case ZohoModule::ORDER:
                $url = $this->helperData->getAPIOrderURL();
                break;
            case ZohoModule::INVOICE:
                $url = $this->helperData->getAPIInvoiceURL();
                break;
        }
        if (!$url) {
            throw new LocalizedException(__('Invalid url'));
        }

        return $url;
    }

    /**
     * @param null|string $type
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function syncs($type = null)
    {
        $queueCollection = $this->getQueueCollectionByType($type);

        return $this->syncQueues($queueCollection);
    }

    /**
     * @param array $ids
     * @param null $type
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function syncByIds($ids, $type = null)
    {
        $queueCollection = $this->getQueueCollectionByType($type)->addFieldToFilter('queue_id', ['IN' => $ids]);

        return $this->syncQueues($queueCollection);
    }

    /**
     * @param null|string $type
     *
     * @return mixed
     */
    public function getAllIds($type = null)
    {
        return $this->getQueueCollectionByType($type)->getAllIds();
    }

    /**
     * @param QueueCollection $queues
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQueueData($queues)
    {
        $queueData = [];

        foreach ($queues as $queue) {
            if ((int) $queue->getStatus() === QueueStatus::SUCCESS) {
                continue;
            }

            $queueData[$queue->getZohoModule()][$queue->getAction()][] = $this->syncQueueObject($queue);
        }

        return $queueData;
    }

    /**
     * @param array $dataAction
     * @param string $moduleAction
     * @param string $url
     * @param array $queueLog
     *
     * @return array
     */
    public function sliceRequest($dataAction, $moduleAction, &$url, &$queueLog)
    {
        $zohoData = [];
        $i        = 0;
        $limit    = 0;
        foreach ($dataAction as $record) {
            // Limit 100 record for every request
            if ($i === 99) {
                $limit++;
                $i = 0;
            }

            $queue = $record['queue'];
            if (!$url) {
                $url = $record['url'];
            }
            if ((int) $moduleAction === QueueActions::DELETE) {
                $zohoData[$limit][] = $record['mapping']['id'];
            } else {
                $zohoData[$limit]['data'][] = $record['mapping'];
            }

            $queueLog[$limit]['data'][] = $queue;
            $i++;
        }

        return $zohoData;
    }

    /**
     * @param array $zohoData
     * @param string $moduleAction
     * @param string $url
     * @param array $queueLog
     *
     * @throws Exception
     */
    public function processRequest($zohoData, $moduleAction, $url, $queueLog)
    {
        $responses = [];
        foreach ($zohoData as $data) {
            $dataRequest = '';
            if ((int) $moduleAction === QueueActions::DELETE) {
                $ids    = implode(',', $data);
                $url    = $url . '?ids=' . $ids;
                $method = Request::METHOD_DELETE;
            } else {
                $dataRequest            = $data;
                $dataRequest['trigger'] = ['approval', 'workflow', 'blueprint'];
                $method                 = ($moduleAction === QueueActions::UPDATE) ?
                    Request::METHOD_PUT : Request::METHOD_POST;
            }

            $response = $this->helperData->sendRequest($url, $method, $dataRequest);

            $responses[] = $response;
        }

        $this->buildDataAndSave($responses, $queueLog);
    }

    /**
     * @param QueueCollection $queues
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function syncQueues($queues)
    {
        $this->countSyncSuccess = 0;
        $queueData              = $this->getQueueData($queues);

        foreach ($queueData as $moduleData) {
            $url = '';
            foreach ($moduleData as $moduleAction => $dataAction) {
                $queueLog = [];
                $zohoData = $this->sliceRequest($dataAction, $moduleAction, $url, $queueLog);
                $this->processRequest($zohoData, $moduleAction, $url, $queueLog);
            }
        }

        return $this->countSyncSuccess;
    }

    /**
     * @param array $responses
     * @param array $queueLog
     */
    public function buildDataAndSave($responses, $queueLog)
    {
        $countSuccess   = 0;
        $zohoEntities   = [];
        $queueData      = [];
        $lastQueueModel = '';

        foreach ($responses as $responseKey => $response) {
            if (isset($response['data']) && is_array($response['data'])) {
                foreach ($response['data'] as $field => $info) {
                    $queue       = $queueLog[$responseKey]['data'][$field];
                    $status      = $info['code'] === 'SUCCESS' ? QueueStatus::SUCCESS : QueueStatus::ERROR;
                    $queueData[] = [
                        'status'        => $status,
                        'json_response' => self::jsonEncode($info),
                        'queue_id'      => $queue->getId(),
                        'total_sync'    => $queue->getTotalSync() + 1
                    ];

                    $lastQueueModel = $queue;

                    if ($queue->getAction() === QueueActions::DELETE) {
                        continue;
                    }

                    if ($status === QueueStatus::SUCCESS) {
                        $dataObject                                                  = $queue->getDataObject();
                        $zohoEntities[$queue->getZohoModule()][$dataObject->getId()] = $info['details']['id'];

                        $countSuccess++;
                    }
                }
            }
        }

        if ($lastQueueModel) {
            if ($zohoEntities) {
                $lastQueueModel->getResource()->updateZohoEntity($zohoEntities);
            }
            if ($queueData) {
                $lastQueueModel->getResource()->updateQueues($queueData);
            }
        }

        $this->countSyncSuccess += $countSuccess;
    }

    /**
     * @param Queue $queue
     *
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function syncQueueObject($queue)
    {
        $sync = $this->syncFactory->create()->load($queue->getSyncId());

        $queueAction = (int) $queue->getAction();
        $object      = $this->getObjectModel($queue);
        $record      = [];
        if ($queueAction === QueueActions::UPDATE) {
            $record       = $this->getDataMapping($sync, $object);
            $record['id'] = $this->getZohoId($queue, $object);
        } elseif ($queueAction === QueueActions::CREATE) {
            $record = $this->getDataMapping($sync, $object);
        } elseif ($queueAction === QueueActions::DELETE) {
            $record['id'] = $queue->getObject();
        }

        $data['mapping'] = $record;
        $queue->setDataObject($object);
        $data['queue'] = $queue;
        $data['url']   = $this->getUrl($queue->getZohoModule());

        return $data;
    }

    /**
     * @param Queue $queue
     * @param Product|Customer|Order|Invoice|Rule $object
     *
     * @return mixed
     */
    public function getZohoId($queue, $object)
    {
        $field = 'zoho_entity';
        if ($queue->getZohoModule() === ZohoModule::LEAD) {
            $field = 'zoho_lead_entity';
        } elseif ($queue->getZohoModule() === ZohoModule::CONTACT) {
            $field = 'zoho_contact_entity';
        }

        return $object->getData($field);
    }

    /**
     * @param Product|Customer|Order|Invoice|Rule $dataObject
     * @param Queue $queue
     * @param array $response
     */
    public function updateZohoEntity($dataObject, $queue, $response)
    {
        if ($dataObject instanceof Customer) {
            $field = 'zoho_entity';
            if ($queue->getZohoModule() === ZohoModule::LEAD) {
                $field = 'zoho_lead_entity';
            } elseif ($queue->getZohoModule() === ZohoModule::CONTACT) {
                $field = 'zoho_contact_entity';
            }
            $resource = $dataObject->getResource();
            $resource->getConnection()->update(
                $resource->getTable('customer_entity'),
                [
                    $field => $response['details']['id'],
                ],
                $resource->getConnection()->quoteInto('entity_id = ?', $dataObject->getId())
            );
        } else {
            $dataObject->setQueueSave(1)->setZohoEntity($response['details']['id'])->save();
        }
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function getQueueCollectionByType($type = null)
    {
        $queueCollection = $this->queueFactory->create()
            ->getCollection();
        if ($type) {
            $queueCollection->addFieldToFilter('zoho_module', $type);
        }

        $queueCollection->addFieldToFilter('status', [
            ['eq' => QueueStatus::PENDING],
            ['eq' => QueueStatus::ERROR]
        ]);

        $queueCollection->addFieldToFilter('total_sync', ['lt' => 5]);

        if ($this->getLimitObjectSend()) {
            $queueCollection->setPageSize($this->getLimitObjectSend());
        }

        return $queueCollection;
    }

    /**
     * @param array $mappingField
     * @param \Mageplaza\ZohoCRM\Model\Sync $sync
     * @param Product|Customer|Order|Invoice|Rule $object
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function processMappingField($mappingField, $sync, $object)
    {
        $value = $mappingField['value'];
        if ($mappingField['value']) {
            $data       = $this->helperMapping->matchData($mappingField['value']);
            $dataFields = [];
            foreach ($data as $field) {
                if (!isset($dataFields[$field])) {
                    $currentValue = '';
                    switch ($sync->getMagentoObject()) {
                        case MagentoObject::CUSTOMER:
                            $currentValue = $this->processCustomerField($field, $object);

                            break;
                        case MagentoObject::CATALOG_RULE:
                            $currentValue = $this->processCatalogRuleField($field, $object);

                            break;
                        case MagentoObject::PRODUCT:
                            $currentValue = $this->processProductField($field, $object);

                            break;
                        case MagentoObject::ORDER:
                        case MagentoObject::INVOICE:
                            $currentValue = $this->processOrderField($field, $object);

                            break;
                    }

                    if ($currentValue) {
                        $value = $this->replaceValue($field, $currentValue, $value);
                    }

                    $dataFields[$field] = $currentValue;
                }
            }

            if (!$value) {
                $value = $mappingField['default'];
            }

            return $this->formatValue($value, $mappingField['type']);
        }

        return $mappingField['default'];
    }

    /**
     * @param string $search
     * @param string $replace
     * @param string $value
     *
     * @return mixed
     */
    public function replaceValue($search, $replace, $value)
    {
        return str_replace('{{' . $search . '}}', $replace, $value);
    }

    /**
     * @param mixed $value
     * @param string $type
     *
     * @return string
     */
    public function formatValue($value, $type)
    {
        if ($value) {
            /**
             * Replace all option match in {{}}
             */
            $value = preg_replace(self::PATTERN_OPTIONS, '', $value);
        }

        switch ($type) {
            case 'int':
                $value = (int) $value;
                break;
            case 'float':
                $value = (float) $value;
                break;
            case 'boolean':
                $value = (bool) $value;
                break;
            case 'date':
                $value = $value ? date('Y-m-d', strtotime($value)) : '';
                break;
            case 'string':
                $value = (string) $value;
        }

        return $value;
    }

    /**
     * @param string $field
     * @param Customer $object
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function processCustomerField($field, $object)
    {
        if ($object == null) {
            return '';
        }

        if ($object instanceof Address) {
            if (str_contains($field, 'billing_')) {
                $field = str_replace('billing_', '', $field);
            }
            if (str_contains($field, 'shipping_')) {
                $field = str_replace('shipping_', '', $field);
            }

            return $object->getData($field);
        }

        if (str_contains($field, 'shipping_')) {
            $field = str_replace('shipping_', '', $field);
            if ($object->getDefaultShippingAddress()) {
                return $object->getDefaultShippingAddress()->getData($field);
            }

            return '';
        }

        if (str_contains($field, 'billing_')) {
            $field = str_replace('billing_', '', $field);
            if ($object->getDefaultBillingAddress()) {
                return $object->getDefaultBillingAddress()->getData($field);
            }

            return '';
        }

        if ($field === 'website') {
            if ($object->getStore()) {
                return $object->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
            }

            $store = $this->storeManager->getStore($object->getStoreId());

            return $store ? $store->getBaseUrl(UrlInterface::URL_TYPE_WEB) : '';
        }

        if ($field === 'name') {
            return $object->getName();
        }

        return $object->getData($field);
    }

    /**
     * @param string $field
     * @param Rule $object
     *
     * @return mixed
     */
    public function processCatalogRuleField($field, $object)
    {
        return $object->getData($field);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function formatDate($value)
    {
        try {
            $date = (new DateTime($value))->format('Y-m-d');
        } catch (Exception $e) {
            $date = '';
        }

        return $date;
    }

    /**
     * @param string $field
     * @param Product $product
     *
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function processProductField($field, $product)
    {
        if ($field === 'tax_class_id') {
            return $this->getTaxName($product->getData($field));
        }

        if ($field === 'category_ids') {
            return $this->getCategoryName($product->getData($field));
        }

        if ($field === 'qty' && $product->getData('quantity_and_stock_status')) {
            $stock = $product->getData('quantity_and_stock_status');
            if (is_array($stock) && isset($stock['qty'])) {
                return $stock['qty'];
            }

            if ($product->getData('stock_data') && is_array($product->getData('stock_data'))) {
                return $product->getData('stock_data')['qty'];
            }
        }

        if ($product->getResource()) {
            if ($productAttribute = $product->getResource()->getAttribute($field)) {
                return $productAttribute->getFrontend()->getValue($product);
            }
        }

        return $product->getData($field);
    }

    /**
     * @param string $field
     * @param Order|Invoice $object
     *
     * @return mixed
     */
    public function processOrderField($field, $object)
    {
        if ($field !== 'shipping_description' &&
            $field !== 'shipping_method' &&
            stripos($field, 'shipping_') !== false
        ) {
            $shippingField = str_replace('shipping_', '', $field);
            if ($object->getShippingAddress()) {
                return $object->getShippingAddress()->getData($shippingField);
            }
        }

        if (stripos($field, 'billing_') !== false) {
            $billingField = str_replace('billing_', '', $field);
            if ($object->getShippingAddress()) {
                return $object->getBillingAddress()->getData($billingField);
            }
        }

        return $object->getData($field);
    }

    /**
     * @param Product|Customer|Order|Invoice|Rule|DataObject|array $oldObject
     * @param Product|Customer|Order|Invoice|Rule $currentObject
     * @param string $type
     *
     * @throws NoSuchEntityException
     */
    public function updateObject($oldObject, $currentObject, $type)
    {
        /**
         * @var \Mageplaza\ZohoCRM\Model\Sync $sync
         */
        $sync = $this->getSyncRule($currentObject, $type);
        if ($sync && $sync->getId()) {
            if (is_array($oldObject)) {
                $oldObject = new DataObject($oldObject);
            }

            $oldData     = $this->getDataMapping($sync, $oldObject);
            $currentData = $this->getDataMapping($sync, $currentObject);
            $result      = ($oldData === $currentData);

            if (!$result) {
                if ($currentObject instanceof Address) {
                    $currentObject = $currentObject->getCustomer();
                }
                $hasRecordUpdate = $this->hasQueue(
                    $currentObject->getId(),
                    $sync->getZohoModule(),
                    $sync->getMagentoObject()
                );

                if ($hasRecordUpdate->getId() &&
                    $hasRecordUpdate->getValidateWebsiteId($sync, $currentObject) &&
                    $hasRecordUpdate->getSyncId() !== $sync->getId()
                ) {
                    $hasRecordUpdate->setSyncId($sync->getId())->save();
                } else {
                    if (!$hasRecordUpdate->getId() ||
                        ($hasRecordUpdate->getId() && $hasRecordUpdate->getStatus() === QueueStatus::SUCCESS)) {
                        /**
                         * @var Queue $queue
                         */
                        $queue = $this->queueFactory->create();
                        $queue->createQueue($sync, $currentObject);
                    }
                }
            }
        }
    }

    /**
     * @param string $type
     * @param Product|Customer|Order|Invoice|Rule $object
     *
     * @return bool|Queue
     * @throws NoSuchEntityException
     */
    public function addObjectToQueue($type, $object)
    {
        /**
         * @var Sync $sync
         */
        $sync     = $this->getSyncRule($object, $type);
        $objectId = $object->getId();
        if ($sync && $sync->getId() &&
            !$this->hasQueue($objectId, $sync->getZohoModule(), $sync->getMagentoObject(), false)->getId()
        ) {
            /**
             * @var Queue $queue
             */
            $queue      = $this->queueFactory->create();
            $websiteIds = explode(',', $sync->getWebsiteIds());
            $id         = $queue->validateWebsite($sync->getMagentoObject(), $websiteIds, $object);
            if ($id) {
                $data = $queue->buildQueueData($object->getId(), $sync, QueueActions::CREATE, $id);
                $queue->addData($data)->save();

                return $queue;
            }
        }

        return false;
    }

    /**
     * @param Product|Customer|Order|Invoice|Rule $object
     * @param string $zohoModule
     *
     * @return Sync|null
     */
    public function getSyncRule($object, $zohoModule)
    {
        foreach ($this->getSyncCollection($zohoModule) as $sync) {
            if ($sync->getConditions()->validate($object)) {
                return $sync;
            }
        }

        return null;
    }

    /**
     * @param string $zohoModule
     *
     * @return AbstractCollection
     */
    public function getSyncCollection($zohoModule)
    {
        return $this->syncFactory->create()->getCollection()
            ->addFieldToFilter('status', Status::ACTIVE)
            ->addFieldToFilter('zoho_module', $zohoModule)
            ->setOrder('priority', 'ASC');
    }

    /**
     * @param string $objectId
     * @param string $zohoModule
     * @param string $magentoObject
     *
     * @return mixed
     */
    public function hasRecordUpdate($objectId, $zohoModule, $magentoObject)
    {
        return $this->hasQueue($objectId, $zohoModule, $magentoObject);
    }

    /**
     * @param string $objectId
     * @param string $zohoModule
     * @param string $magentoObject
     * @param bool $isUpdate
     *
     * @return mixed
     */
    public function hasQueue($objectId, $zohoModule, $magentoObject, $isUpdate = true)
    {
        $queue = $this->queueFactory->create()
            ->getCollection()
            ->addFieldToFilter('object', $objectId)
            ->addFieldToFilter('zoho_module', $zohoModule)
            ->addFieldToFilter('magento_object', $magentoObject);
        if ($isUpdate) {
            $queue->addFieldToFilter('action', QueueActions::UPDATE);
            $queue->addFieldToFilter('status', QueueStatus::PENDING);
        } else {
            $queue->addFieldToFilter('action', QueueActions::CREATE);
        }

        return $queue->getFirstItem();
    }

    /**
     * @param \Mageplaza\ZohoCRM\Model\Sync $sync
     * @param Product|Customer|Order|Invoice|Rule $object
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataMapping($sync, $object)
    {
        $magentoObject = $sync->getMagentoObject();
        $mapping       = self::jsonDecode($sync->getMapping());

        $record = [];
        foreach ($mapping as $field => $mappingField) {
            $record[$field] = $this->processMappingField($mappingField, $sync, $object);
        }

        if ($magentoObject === MagentoObject::CATALOG_RULE) {
            $record['Status'] = $record['Status'] ? 'Active' : 'InActive';
        }

        if ($magentoObject === MagentoObject::PRODUCT) {
            if (!isset($record['Product_Active']) || $record['Product_Active'] === '2') {
                $record['Product_Active'] = false;
            } else {
                $record['Product_Active'] = true;
            }

            if ($record['Tax']) {
                $record['Taxable'] = true;
                $record['Tax']     = [$record['Tax']];
            }
        }

        if ($magentoObject === MagentoObject::CUSTOMER && isset($record['Email_Opt_Out'])) {
            $record['Email_Opt_Out'] = (bool) $record['Email_Opt_Out'];
        }

        if (in_array($magentoObject, [MagentoObject::ORDER, MagentoObject::INVOICE], true)) {
            $record['Product_Details'] = $this->processItems($magentoObject, $object);
            $record['Adjustment']      = 0;
            $record['Discount']        = 0;

            $customerId = $magentoObject === MagentoObject::ORDER ?
                $object->getCustomerId() : $object->getOrder()->getCustomerId();
            $customer   = $this->getCustomerById($customerId);
            if ($customer->getZohoContactEntity()) {
                $record['Contact_Name']['id'] = $customer->getZohoContactEntity();
            }
            if ($magentoObject === MagentoObject::INVOICE && $customer->getZohoEntity()) {
                $record['Account_Name'] = $customer->getZohoEntity();
            }

            $this->setDefaultSubject($magentoObject, $object, $record);
        }

        return $record;
    }

    /**
     * @param string $magentoObject
     * @param Order|Invoice $object
     * @param array $record
     */
    public function setDefaultSubject($magentoObject, $object, &$record)
    {
        if (!isset($record['Subject']) || !$record['Subject']) {
            if ($magentoObject === MagentoObject::ORDER) {
                $record['Subject'] = 'Purchase Order #' . $object->getIncrementId();
            } else {
                $record['Subject']        = 'Invoice #' . $object->getIncrementId();
                $record['Purchase_Order'] = $object->getOrder()->getIncrementId();
            }
        }
    }

    /**
     * @param string $magentoObject
     * @param Order|Invoice $object
     *
     * @return array
     */
    public function processItems($magentoObject, $object)
    {
        $productDetails = [];
        $items          = $magentoObject === MagentoObject::ORDER ? $object->getAllVisibleItems() : $object->getItems();
        if ($items) {
            foreach ($items as $item) {
                if (!$item->hasRowTotal()) {
                    continue;
                }
                $productId = $this->productFactory->create()->getIdBySku($item->getSku());
                $product   = $this->productFactory->create()->load($productId);
                $qty              = $magentoObject === MagentoObject::ORDER ? $item->getQtyOrdered() : $item->getQty();
                $productDetails[] = [
                    'product'              => [
                        'id' => $product->getZohoEntity()
                    ],
                    'quantity'             => (int) $qty,
                    'Discount'             => (float) $item->getBaseDiscountAmount(),
                    'total'                => (float) $item->getBaseRowTotal(),
                    'list_price'           => (float) $item->getBasePrice(),
                    'unit_price'           => (float) $item->getBasePrice(),
                    'net_total'            => (float) ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()),
                    'total_after_discount' => (float) ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()),
                    'Tax'                  => (float) $item->getBaseTaxAmount()
                ];
            }
        }

        return $productDetails;
    }

    /**
     * @param string $taxId
     *
     * @return mixed
     */
    public function getTaxName($taxId)
    {
        if (!isset($this->taxRepository[$taxId])) {
            $tax                         = $this->taxFactory->create()->load($taxId);
            $this->taxRepository[$taxId] = $tax;
        }

        return $this->taxRepository[$taxId]->getClassName();
    }

    /**
     * @param array $categoryIds
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCategoryName($categoryIds)
    {
        $names = [];
        if ($categoryIds && is_array($categoryIds)) {
            foreach ($categoryIds as $id) {
                if (!empty($names)) {
                    return implode(', ', $names);
                }
                $category = $this->categoryRepository->get($id);
                if ($category) {
                    $names[] = $category->getName();
                }
            }
        }

        return implode(', ', $names);
    }

    /**
     * @param string $ruleId
     *
     * @return mixed
     */
    public function getCatalogRuleById($ruleId)
    {
        if (!isset($this->catalogRules[$ruleId])) {
            /** @var Rule $rule */
            $rule = $this->ruleFactory->create();

            $rule->load($ruleId);
            $this->catalogRules[$ruleId] = $rule;
        }

        return $this->catalogRules[$ruleId];
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getCustomerById($id)
    {
        if (!isset($this->customerRepository[$id])) {
            /** @var Customer $customer */
            $customer = $this->customerFactory->create();

            $customer->load($id);
            $this->customerRepository[$id] = $customer;
        }

        return $this->customerRepository[$id];
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getOrderById($id)
    {
        if (!isset($this->orderRepository[$id])) {
            $order = $this->orderFactory->create();

            $order->load($id);
            $this->orderRepository[$id] = $order;
        }

        return $this->orderRepository[$id];
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getInvoiceById($id)
    {
        if (!isset($this->invoiceRepository[$id])) {
            $invoice = $this->invoiceFactory->create();

            $invoice->load($id);
            $this->invoiceRepository[$id] = $invoice;
        }

        return $this->invoiceRepository[$id];
    }

    /**
     * @param string $number
     */
    public function setLimitObjectSend($number)
    {
        $this->limitObjectSend = $number;
    }

    /**
     * @return int
     */
    public function getLimitObjectSend()
    {
        return $this->limitObjectSend;
    }
}
