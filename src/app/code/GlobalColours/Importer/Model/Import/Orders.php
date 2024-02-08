<?php

namespace GlobalColours\Importer\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Class Courses
 */
class Orders extends AbstractEntity
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    private $_product;
    private $quote;
    private $quoteManagement;
    private $customerRepository;
    protected $invoiceService;
    protected $transaction;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private  $catalogProduct;

    private $createdOrders = [];

    const ENTITY_CODE = "orders";
    const TABLE = "sales_order";
    const ENTITY_ID_COLUMN = "entity_id";

    /**
     * If we should check column names
     */
    protected $needColumnCheck = true;

    /**
     * Need to log in import history
     */
    protected $logInHistory = true;

    /**
     * Permanent entity columns.
     */
    protected $_permanentAttributes = [
        // 'attribute_code'
    ];

    /**
     * Valid column names
     */
    protected $validColumnNames = ["external_id", "order_status", "order_currency", "customer_note", "billing_first_name", "billing_last_name", "billing_street", "billing_city", "billing_postcode", "billing_country", "billing_region", "customer_email", "billing_phone", "shipping_first_name", "shipping_last_name", "shipping_street", "shipping_city", "shipping_postcode", "shipping_country", "shipping_region", "item_sku", "quantity", "total_price", "payment_method", "shipping_method"];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Courses constructor.
     *
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        LoggerInterface $logger,
        Product $product,
        QuoteFactory $quote,
        QuoteManagement $quoteManagement,
        CustomerRepositoryInterface $customerRepository,
        InvoiceService $invoiceService,
        Transaction $transaction
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->initMessageTemplates();
        $this->logger = $logger;
        $this->_product = $product;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerRepository = $customerRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        return true;
        // $code = $rowData['attribute_code'] ?? '';
        // $attributeSetName = $rowData['attribute_set_name'] ?? '';
        // $attributeType = $rowData['attribute_type'] ?? '';

        // if (!$code) {
        //     $this->addRowError('AttributeCodeIsRequired', $rowNum);
        // }

        // if (!$attributeSetName) {
        //     $this->addRowError('AttributeSetNameIsRequired', $rowNum);
        // }

        // if ($attributeType && ($attributeType !== "select" || $attributeType !== "multiselect")) {
        //     $this->addRowError('AttributeTypeIsNotValid', $rowNum);
        // }

        // if (isset($this->_validatedRows[$rowNum])) {
        //     return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        // }

        // $this->_validatedRows[$rowNum] = true;

        // return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                // $this->deleteEntity();
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->saveAndReplaceEntity();
                break;
            case Import::BEHAVIOR_APPEND:
                $this->saveAndReplaceEntity();
                break;
        }

        return true;
    }

    /**
     * Delete entities
     *
     * @return bool
     */
    private function deleteEntity(): bool
    {
        return true;
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);

                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowId = $rowData[static::ENTITY_ID_COLUMN];
                    $rows[] = $rowId;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }

        if ($rows) {
            return $this->deleteEntityFinish(array_unique($rows));
        }

        return false;
    }


    private function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $rows = [];
        $entityList = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {

            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);

                    continue;
                }

                $entityList[] = $row;
            }
        }
        if (Import::BEHAVIOR_REPLACE === $behavior) {
            // if ($rows && $this->deleteEntityFinish(array_unique($rows))) {
            $this->saveEntityFinish($entityList);
            // }
        } elseif (Import::BEHAVIOR_APPEND === $behavior) {
            $this->saveEntityFinish($entityList);
        }
    }

    private function saveEntityFinish(array $entityData): bool
    {
        if ($entityData) {
            $rows = [];

            foreach ($entityData as $entityRows) {
                $rows[] = $entityRows;
            }

            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    if (in_array($row["external_id"], $this->createdOrders)) {
                        continue;
                    }

                    $tempOrder = [
                        'currency_id'  => $row["order_currency"],
                        'email'        => $row["customer_email"],
                        'payment_method' => $row["payment_method"],
                        'shipping_method' => $row["shipping_method"],
                        'order_status' => $row["order_status"],
                        'shipping_address' => [
                            'firstname'    => $row["shipping_first_name"],
                            'lastname'     => $row["shipping_last_name"],
                            'street' => $row["shipping_street"],
                            'city' => $row["shipping_city"],
                            'country_id' => $row["shipping_country"],
                            'region' => $row["shipping_region"],
                            'postcode' => $row["shipping_postcode"],
                            'telephone' => $row["billing_phone"],
                        ],
                        'billing_address' => [
                            'firstname'    => $row["billing_first_name"],
                            'lastname'     => $row["billing_last_name"],
                            'telephone' => $row["billing_phone"],
                            'street' => $row["billing_street"],
                            'city' => $row["billing_city"],
                            'country_id' => $row["billing_country"],
                            'region' => $row["billing_region"],
                            'postcode' => $row["billing_postcode"],
                        ],
                        'items' => [
                            ['sku' => $row["item_sku"], 'qty' => $row["quantity"], 'price' => $row["total_price"]]
                        ],
                    ];

                    $is_first = true;
                    foreach ($rows as $i => $order) {
                        if ($order['external_id'] === $row["external_id"] && !$is_first) {
                            $newItem = ['sku' => $order["item_sku"], 'qty' => $order["quantity"], 'price' => $order["total_price"]];
                            array_push($tempOrder['items'], $newItem);
                        }
                        $is_first = false;
                    }

                    array_push($this->createdOrders, $row["external_id"]);

                    $res = $this->createOrder($tempOrder);
                    if (!isset($res['error'])) {
                        $this->countItemsCreated++;
                    } else {
                        $this->logger->info("Order creation failed => ", $res);
                    }
                }
            }
        }
        return true;
    }

    /**
     * Create Order On Your Store
     * 
     * @param array $orderData
     * @return array
     * 
     */
    private function createOrder($orderData)
    {
        try {
            $customer = $this->customerRepository->get($orderData['email']);
        } catch (NoSuchEntityException $e) {
            return ['error' => 1, 'msg' => 'Customer (' . $orderData['email'] . ') not found!'];
        }

        $quote = $this->quote->create();
        $quote->assignCustomer($customer);

        //add items in quote
        foreach ($orderData['items'] as $item) {
            $productId = $this->_product->getIdBySku($item['sku']);
            if (!$productId) {
                return ['error' => 1, 'msg' => 'Product (' . $item['sku'] . ') not found!'];
            }
            $product = $this->_product->load($productId);

            // if (!$product->isSalable()) {
            //     return ['error' => 1, 'msg' => 'Product (' . $item['sku'] . ') is not available for purchase.'];
            // }
            // if (!$product->isAvailable()) {
            //     return ['error' => 1, 'msg' => 'Product (' . $item['sku'] . ') is not available in stock.'];
            // }

            $product->setPrice($item['price']);

            $quote->addProduct(
                $product,
                intval($item['qty'])
            );
        }

        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['billing_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        // Collect Rates and Set Shipping & Payment Method

        $paymentMethod = $this->getPaymentMethodCode($orderData["payment_method"]);

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate');
        $quote->setPaymentMethod($paymentMethod);
        $quote->setInventoryProcessed(true);
        $quote->save();

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => $paymentMethod]);

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);

        $orderStatus = $this->getStatusCode($orderData["order_status"]);

        $order->setEmailSent(0);
        $order->setState($orderStatus);
        $order->setStatus($orderStatus);
        $order->save();
        if ($orderStatus == "complete")
            $this->createInvoice($order);
        if ($order->getEntityId()) {
            $increment_id = $order->getRealOrderId();
            $result['order_id'] = $increment_id;
        } else {
            $result = ['error' => 1, 'msg' => 'Failed to save!'];
        }
        return $result;
    }

    private function getStatusCode($status)
    {
        switch ($status) {
            case 'wc-completed':
                return 'complete';
                break;
            case 'wc-cancelled':
                return 'canceled';
                break;
            case 'wc-pending':
                return 'pending';
                break;
            case 'wc-processing':
                return 'processing';
                break;
            case 'wc-refunded':
                return 'closed';
                break;
            case 'wc-failed':
                return 'canceled';
                break;
            default:
                return 'canceled';
                break;
        }
    }

    private function getPaymentMethodCode($paymentMethod)
    {
        switch ($paymentMethod) {
                // case 'Direct bank transfer':
                //     return 'banktransfer';
                //     break;
                // case 'paypal':
                //     return 'paypal_express';
                //     break;
            default:
                return 'checkmo';
                break;
        }
    }

    private function createInvoice($order)
    {
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->save();
        $transactionSave = $this->transaction->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );
        $transactionSave->save();
        //Send Invoice mail to customer
        $order->addStatusHistoryComment(
            __('Custom invoice created')
        )
            ->setIsCustomerNotified(true)
            ->save();
    }

    /**
     * Delete entities
     *
     * @param array $entityIds
     *
     * @return bool
     */
    private function deleteEntityFinish(array $entityIds): bool
    {
        if ($entityIds) {
            try {
                $this->countItemsDeleted += $this->connection->delete(
                    $this->connection->getTableName(static::TABLE),
                    $this->connection->quoteInto(static::ENTITY_ID_COLUMN . ' IN (?)', $entityIds)
                );

                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Init Error Messages
     */
    private function initMessageTemplates()
    {
        $this->addMessageTemplate(
            'AttributeCodeIsRequired',
            __('The attribute code cannot be empty.')
        );
        $this->addMessageTemplate(
            'AttributeSetNameIsRequired',
            __('The attribute set name cannot be empty.')
        );
        $this->addMessageTemplate(
            'AttributeTypeIsNotValid',
            __('The attribute type is not valid.')
        );
    }
}
