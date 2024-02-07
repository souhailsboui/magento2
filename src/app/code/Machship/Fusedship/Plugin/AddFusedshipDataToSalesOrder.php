<?php declare(strict_types=1);

namespace Machship\Fusedship\Plugin;

use Machship\Fusedship\Model\ResourceModel\SalesOrder\Collection;
use Machship\Fusedship\Model\ResourceModel\SalesOrder\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class AddFusedshipDataToSalesOrder
{
    private $fusedshipSalesOrderCollectionFactory;

    /**
     * AddFusedshipDataToSalesOrder constructor.
     * @param CollectionFactory $fusedshipSalesOrderCollectionFactory
     */
    public function __construct(
        CollectionFactory $fusedshipSalesOrderCollectionFactory
    ) {
        $this->fusedshipSalesOrderCollectionFactory = $fusedshipSalesOrderCollectionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        $result
    ) {
        // We must first grab the record from our custom database table by the order id.

        /** @var Collection $fusedshipSalesOrder */
        $fusedshipSalesOrderCollection = $this->fusedshipSalesOrderCollectionFactory->create();
        $fusedshipSalesOrder = $fusedshipSalesOrderCollection
            ->addFieldToFilter('order_id', $result->getId())
            ->getFirstItem();

        // Then, we get the extension attributes that are currently assigned to this order.
        $extensionAttributes = $result->getExtensionAttributes();

        // We then call "setData" on the property we want to set, wtih the value from our custom table.

        $extensionAttributes->setData('fusedship_origin', $fusedshipSalesOrder->getData('fusedship_origin'));
        $extensionAttributes->setData('fusedship_destination', $fusedshipSalesOrder->getData('fusedship_destination'));
        $extensionAttributes->setData('fusedship_order_items', $fusedshipSalesOrder->getData('fusedship_order_items'));
        $extensionAttributes->setData('fusedship_is_residential', $fusedshipSalesOrder->getData('fusedship_is_residential'));

        // Then, just re-set the extension attributes containing the newly added data...
        $result->setExtensionAttributes($extensionAttributes);

        // ...and finally, return the result.
        return $result;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        $result
    ) {
        // We do the same thing here, and can save some time by passing the logic to afterGet.
        foreach ($result->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $result;
    }
}
