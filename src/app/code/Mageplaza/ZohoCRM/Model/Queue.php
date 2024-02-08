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

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\RuleFactory as CatalogRuleFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ZohoCRM\Model\ResourceModel\Queue as ResourceQueue;
use Mageplaza\ZohoCRM\Model\Source\MagentoObject;
use Mageplaza\ZohoCRM\Model\Source\QueueActions;
use Mageplaza\ZohoCRM\Model\Source\QueueStatus;
use Mageplaza\ZohoCRM\Model\Source\ZohoModule;

/**
 * Class Queue
 * @package Mageplaza\ZohoCRM\Model
 */
class Queue extends AbstractModel
{
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
     * @var CatalogRuleFactory
     */
    protected $catalogRuleFactory;

    /**
     * @var MagentoObjectFactory
     */
    protected $magentoObjectFactory;

    /**
     * @var SyncFactory
     */
    protected $syncFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Queue constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ProductFactory $productFactory
     * @param CustomerFactory $customerFactory
     * @param OrderFactory $orderFactory
     * @param InvoiceFactory $invoiceFactory
     * @param CatalogRuleFactory $catalogRuleFactory
     * @param SyncFactory $syncFactory
     * @param StoreManagerInterface $storeManager
     * @param MagentoObjectFactory $magentoObjectFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory,
        OrderFactory $orderFactory,
        InvoiceFactory $invoiceFactory,
        CatalogRuleFactory $catalogRuleFactory,
        SyncFactory $syncFactory,
        StoreManagerInterface $storeManager,
        MagentoObjectFactory $magentoObjectFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productFactory       = $productFactory;
        $this->customerFactory      = $customerFactory;
        $this->orderFactory         = $orderFactory;
        $this->invoiceFactory       = $invoiceFactory;
        $this->catalogRuleFactory   = $catalogRuleFactory;
        $this->syncFactory          = $syncFactory;
        $this->storeManager         = $storeManager;
        $this->magentoObjectFactory = $magentoObjectFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceQueue::class);
    }

    /**
     * @param Sync $sync
     *
     * @return int|void
     * @throws NoSuchEntityException
     */
    public function addToQueue($sync)
    {
        $countSuccess     = 0;
        $magentoObject    = $sync->getMagentoObject();
        $data             = [];
        $websiteIds       = explode(',', $sync->getWebsiteIds());
        $count            = 0;
        $objectCollection = $this->magentoObjectFactory->getCollection($magentoObject, $sync);
        foreach ($objectCollection as $object) {
            $isValid = $sync->getConditions()->validate($object);
            if (!$isValid || $this->isExistQueue($sync, $object->getId())) {
                continue;
            }

            $id = $this->validateWebsite($magentoObject, $websiteIds, $object);
            if ($id) {
                $data[] = $this->buildQueueData($object->getId(), $sync, QueueActions::CREATE, $id);
                $count++;
                if ($count === 999) {
                    $this->insertQueues($data);
                    $countSuccess += $count;
                    $count        = 0;
                    $data         = [];
                }
            }
        }
        $this->insertQueues($data);

        return $countSuccess + $count;
    }

    /**
     * @param Sync $sync
     * @param string $id
     *
     * @return mixed
     */
    public function isExistQueue($sync, $id)
    {
        $queueCollection = $this->getCollection()
            ->addFieldToFilter('object', $id)
            ->addFieldToFilter('zoho_module', $sync->getZohoModule())
            ->getFirstItem();

        return $queueCollection->getId();
    }

    /**
     * @param Sync $sync
     * @param Product|Customer|Order|Invoice|Rule $object
     *
     * @throws NoSuchEntityException
     */
    public function createQueue($sync, $object)
    {
        $id = $this->getValidateWebsiteId($sync, $object);

        if ($id) {
            $data = $this->buildQueueData($object->getId(), $sync, QueueActions::UPDATE, $id);
            $this->addData($data)->save();
        }
    }

    /**
     * @param Sync $sync
     * @param Product|Customer|Order|Invoice|Rule $object
     *
     * @return int|string
     * @throws NoSuchEntityException
     */
    public function getValidateWebsiteId($sync, $object)
    {
        $websiteIds = explode(',', $sync->getWebsiteIds());

        return $this->validateWebsite($sync->getMagentoObject(), $websiteIds, $object);
    }

    /**
     * @param $data
     */
    public function insertQueues($data)
    {
        if ($data) {
            $this->getResource()->insertQueues($data);
        }
    }

    /**
     * @param Product|Customer|Order|Invoice|Rule $object
     * @param string $type
     *
     * @throws NoSuchEntityException
     */
    public function addDeleteObjectToQueue($object, $type)
    {
        $data = [];
        if ($type === MagentoObject::CUSTOMER) {
            $this->checkAndBuildQueue(
                $object->getZohoEntity(),
                ZohoModule::ACCOUNT,
                $object,
                QueueActions::DELETE,
                $data
            );

            $this->checkAndBuildQueue(
                $object->getZohoLeadEntity(),
                ZohoModule::LEAD,
                $object,
                QueueActions::DELETE,
                $data
            );

            $this->checkAndBuildQueue(
                $object->getZohoContactEntity(),
                ZohoModule::CONTACT,
                $object,
                QueueActions::DELETE,
                $data
            );
        } else {
            $this->checkAndBuildQueue(
                $object->getZohoEntity(),
                $type,
                $object,
                QueueActions::DELETE,
                $data
            );
        }

        $this->insertQueues($data);
    }

    /**
     * @param string $zohoId
     * @param string $type
     * @param Product|Customer|Order|Invoice|Rule $object
     * @param string $action
     * @param array $data
     *
     * @throws NoSuchEntityException
     */
    public function checkAndBuildQueue($zohoId, $type, $object, $action, &$data)
    {
        if ($zohoId) {
            if ($type === MagentoObject::CATALOG_RULE) {
                $type = ZohoModule::CAMPAIGN;
            }
            $sync = $this->syncFactory->create()->load($type, 'zoho_module');
            if ($sync->getId()) {
                $websiteIds = explode(',', $sync->getWebsiteIds());
                $id         = $this->validateWebsite($type, $websiteIds, $object);
                if ($id) {
                    $data[] = $this->buildQueueData($zohoId, $sync, $action, $id);
                }
            }
        }
    }

    /**
     * @param string $type
     * @param array $websiteIds
     * @param Product|Customer|Order|Invoice|Rule $object
     *
     * @return int|string
     * @throws NoSuchEntityException
     */
    public function validateWebsite($type, $websiteIds, $object)
    {
        $id = '';

        if ($type === MagentoObject::CATALOG_RULE || ($type === MagentoObject::PRODUCT && $object->getWebsiteIds())) {
            $catalogRuleWebsiteIds = array_intersect($object->getWebsiteIds(), $websiteIds);
            if ($catalogRuleWebsiteIds) {
                $id = implode(',', $catalogRuleWebsiteIds);
            }
        } else {
            $websiteId = $this->storeManager->getStore($object->getStoreId())->getWebsiteId();
            if (in_array($websiteId, $websiteIds, true)) {
                $id = $websiteId;
            }
        }

        return $id;
    }

    /**
     * @param string $object
     * @param Sync $sync
     * @param string $action
     * @param string $id
     *
     * @return array
     */
    public function buildQueueData($object, $sync, $action, $id)
    {
        return [
            'object'         => $object,
            'magento_object' => $sync->getMagentoObject(),
            'zoho_module'    => $sync->getZohoModule(),
            'website'        => $id,
            'action'         => $action,
            'status'         => QueueStatus::PENDING,
            'sync_id'        => $sync->getId()
        ];
    }

    /**
     * @param Queue $item
     * @param UrlInterface $urlBuilder
     *
     * @return array
     */
    public function getQueueObject($item, $urlBuilder)
    {
        $magentoObject = $item['magento_object'];
        $result        = [];
        $objectId      = $item['object'];

        switch ($magentoObject) {
            case MagentoObject::PRODUCT:
                $product        = $this->productFactory->create()->load($objectId);
                $result['name'] = $product->getSku();
                $result['url']  = $urlBuilder->getUrl('catalog/product/edit', ['id' => $product->getId()]);

                break;
            case MagentoObject::CUSTOMER:
                $customer       = $this->customerFactory->create()->load($objectId);
                $result['name'] = $customer->getName();
                $result['url']  = $urlBuilder->getUrl('customer/index/edit', ['id' => $objectId]);

                break;
            case MagentoObject::ORDER:
                $order          = $this->orderFactory->create()->load($objectId);
                $result['name'] = $order->getIncrementId();
                $result['url']  = $urlBuilder->getUrl('sales/order/view', ['order_id' => $objectId]);

                break;
            case MagentoObject::INVOICE:
                $invoice        = $this->invoiceFactory->create()->load($objectId);
                $result['name'] = $invoice->getIncrementId();
                $result['url']  = $urlBuilder->getUrl('sales/invoice/view', ['invoice_id' => $objectId]);

                break;
            case MagentoObject::CATALOG_RULE:
                $catalogRule    = $this->catalogRuleFactory->create()->load($objectId);
                $result['name'] = $catalogRule->getName();
                $result['url']  = $urlBuilder->getUrl('catalog_rule/promo_catalog/edit', ['id' => $objectId]);

                break;
            default:
        }

        return $result;
    }
}
