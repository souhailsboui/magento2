<?php

namespace Machship\Fusedship\Block\Adminhtml\Product\Edit\Tab;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;


class FusedshipData extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'fusedshipdata.phtml';

    private $registry;

    private $objectManager;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        parent::__construct($context, $data);
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getFusedshipProductData()
    {
        $product_id = $this->getProduct()->getId();

        if(!empty($product_id)) {

            $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');

            $connection = $resource->getConnection();

            $fusedshipProductDataTable  =   $connection->getTableName('fusedship_product_data');
            
            $deployment_config = $this->objectManager->get('Magento\Framework\App\DeploymentConfig');
        
            $table_prefix = $deployment_config->get('db/table_prefix');
            
            if (!empty($table_prefix) && strpos($fusedshipProductDataTable, $table_prefix) === false) {
                $fusedshipProductDataTable = $table_prefix . $fusedshipProductDataTable;
            }


            $select = $connection->select()->from($fusedshipProductDataTable)->where('product_id = :product_id');

            $binds = ['product_id' => (int) $product_id];

            return $connection->fetchAll($select, $binds);
        }

        return;
    }

    public function getProductCartons()
    {
        $product_id = $this->getProduct()->getId();

        if($product_id) {
            $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            $fusedshipProductCartonsTable  =  $connection->getTableName('fusedship_product_cartons');
            
            $deployment_config = $this->objectManager->get('Magento\Framework\App\DeploymentConfig');
        
            $table_prefix = $deployment_config->get('db/table_prefix');
            

            if (!empty($table_prefix) && strpos($fusedshipProductCartonsTable, $table_prefix) === false) {
                $fusedshipProductCartonsTable = $table_prefix . $fusedshipProductCartonsTable;
            }

            $select = $connection->select()->from($fusedshipProductCartonsTable)
                    ->where('product_id = :product_id');
            $binds = ['product_id' => (int) $product_id];

            return $connection->fetchAll($select, $binds);
        }

        return;
    }
}
