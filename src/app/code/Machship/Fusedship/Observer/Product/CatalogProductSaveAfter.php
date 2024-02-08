<?php
namespace Machship\Fusedship\Observer\Product;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfter implements ObserverInterface
{

    protected $objectManager;

    protected $_request;

    public function __construct(\Magento\Framework\App\RequestInterface $request) {
        $this->_request      = $request;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Catalog Product Save After Event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product    = $observer->getProduct();

        $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');

        $connection = $resource->getConnection();

        $fusedshipProductDataTable    =   $connection->getTableName('fusedship_product_data');
        $fusedshipProductCartonsTable =   $connection->getTableName('fusedship_product_cartons');
        
        $deployment_config = $this->objectManager->get('Magento\Framework\App\DeploymentConfig');
        
        $table_prefix = $deployment_config->get('db/table_prefix');
        
        if (!empty($table_prefix) && strpos($fusedshipProductDataTable, $table_prefix) === false) {
            $fusedshipProductDataTable = $table_prefix . $fusedshipProductDataTable;
        }
        
        if (!empty($table_prefix) && strpos($fusedshipProductCartonsTable, $table_prefix) === false) {
            $fusedshipProductCartonsTable = $table_prefix . $fusedshipProductCartonsTable;
        }


        $productId   = $product->getId();
        $postData    = $this->_request->getPostValue();

        $use_fusedship_rates = $postData['use_fusedship_rates'] ?? 0;

        $fusedshipProductDataFound = $connection->select('value_id')
            ->from($fusedshipProductDataTable)
            ->where('product_id = :product_id');

        $binds = ['product_id' => (int) $productId];

        $fusedshipProductDataFound = $connection->fetchAll($fusedshipProductDataFound, $binds);

        if (isset($fusedshipProductDataFound) && !empty($fusedshipProductDataFound)) {
            $updatQuery = "UPDATE " . $fusedshipProductDataTable . " SET use_fusedship_rates='".$use_fusedship_rates."' WHERE product_id = '".$productId."'";
            $connection->query($updatQuery);
        } else {
            $insertQuery = "INSERT INTO " . $fusedshipProductDataTable . "(product_id, use_fusedship_rates) VALUES ('$productId', '".$use_fusedship_rates."')";
            $connection->query($insertQuery);
        }


        $deleteQuery = "DELETE FROM " . $fusedshipProductCartonsTable . " WHERE product_id = '".$productId."'";
        $connection->query($deleteQuery);

        $carton_length  = $postData['carton_length'] ?? [];
        $carton_width   = $postData['carton_width'] ?? [];
        $carton_height  = $postData['carton_height'] ?? [];
        $carton_weight  = $postData['carton_weight'] ?? [];
        $package_type   = $postData['package_type'] ?? [];


        if(is_array($carton_length)) {

            for ($i = 0; $i < count($carton_length); $i++) {
                $insertCartonQuery = "INSERT INTO " . $fusedshipProductCartonsTable . "(product_id, carton_length, carton_width, carton_height, carton_weight ,package_type) VALUES ('$productId', '".$carton_length[$i]."', '".$carton_width[$i]."', '".$carton_height[$i]."', '".$carton_weight[$i]."','".$package_type[$i]."')";

                $connection->query($insertCartonQuery);
            }
        }

    }
}
