<?php
namespace Machship\Fusedship\Block\Adminhtml\Order\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class CustomInfo extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * CustomFieldsRepositoryInterface
     *
     * @var CustomFieldsRepositoryInterface
     */
    protected $customFieldsRepository;

    protected $objectManager;

    /**
     * CustomFields constructor.
     *
     * @param Context                         $context                Context
     * @param Registry                        $registry               Registry
     * @param CustomFieldsRepositoryInterface $customFieldsRepository CustomFieldsRepositoryInterface
     * @param array                           $data                   Data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get current order
     *
     * @return Order
     */
    public function getOrder() : Order
    {
        return $this->coreRegistry->registry('current_order');
    }

    public function getFusedshipSalesOrder($orderId) {
        $connection                 = $this->objectManager->get('\Magento\Framework\App\ResourceConnection');
        $connection                 = $connection->getConnection();
        $connectionResource         = $this->objectManager->create('\Magento\Framework\App\ResourceConnection');
        $fusedshipSalesOrderTable   = $connectionResource->getTableName('fusedship_sales_order');


        $fusedshipSalesOrderFound = $connection->select('*')
            ->from($fusedshipSalesOrderTable)
            ->where('order_id = :order_id');


        $binds = ['order_id' => (int) $orderId];

        return $connection->fetchAll($fusedshipSalesOrderFound, $binds);
    }
}