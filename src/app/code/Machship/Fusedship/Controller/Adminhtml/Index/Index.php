<?php

namespace Machship\Fusedship\Controller\Adminhtml\Index;


class Index extends \Magento\Backend\App\Action
{

    public function execute()
    {

    	$this->_view->loadLayout();

    	$post = $this->getRequest()->getPostValue();
        if($post){

        	if($arr = $this->migrate($post)){

        		$errors = '<div class="errors">';
        		foreach($arr['error'] as $key =>$name){
        			if($key == 0){
        				$errors .= "<br /><br /><label>Errors <small><a href='#showerrors' id='show-errors-btn' onClick='jQuery(\".errors .list\").toggle()'>[show/hide]</a></small></label><div class='list'>";
        			}
        			$errors .= "<strong>".$name."</strong> - Missing height, width, length. <br />";
        		}
        		$errors .= "</div></div>";

    			$this->_view->getLayout()->getBlock('migrate_block_adminhtml_index_index')->setMessage('Shipping dimensions attributes have been copied to Machship box settings.'.$errors);
        	}else{
        		$this->_view->getLayout()->getBlock('migrate_block_adminhtml_index_index')->setMessage('No products were updated.');
        	}
        }else{
        	$this->_view->getLayout()->initMessages();
        }

        $this->_view->renderLayout();

	}

	public function migrate($post){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        $weightUnit = $fusedshipHelper->getWeightUnit() ?? 'lbs';

		$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');

        // Add filters to include enable products only
        $productCollection->addFieldToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);


        $productCollection = $productCollection->addAttributeToSelect('id')
                                ->addAttributeToSelect('name')
                                ->addAttributeToSelect('type_id');

        $postLength = $post['length'] == 'use_native_attribute' ? 'ts_dimensions_length' : $post['length'];
        $postWidth  = $post['width'] == 'use_native_attribute' ? 'ts_dimensions_width' : $post['width'];
        $postHeight = $post['height'] == 'use_native_attribute' ? 'ts_dimensions_height' : $post['height'];
        $postWeight = $post['weight'] == 'use_native_attribute' ? 'weight' : $post['weight'];

        $productCollection->addAttributeToSelect($postLength);
        $productCollection->addAttributeToSelect($postWidth);
        $productCollection->addAttributeToSelect($postHeight);
        $productCollection->addAttributeToSelect($postWeight);

        if (!(stripos($post['package_type'], 'static_pt_') !== false)) {
            $productCollection->addAttributeToSelect($post['package_type']);
        }

        $collection = $productCollection->load();


		$this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');

        $connection = $this->_resources->getConnection();

        $connectionResource   =   \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\App\ResourceConnection');

        $fusedship_product_cartons_table  =   $connectionResource->getTableName('fusedship_product_cartons');
        // $fusedship_product_data_table     =   $connectionResource->getTableName('fusedship_product_data');

        $affected_products = [];
        $error_products = [];
        $skipped_products = [];

        // db row variable arrays
        $delete_rows = [];
        $insert_rows = [];

		foreach ($collection as $product){

		    $id    = $product->getId();
		    $name  = $product->getName();

            if($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
				$skipped_products[] = $name;
                continue;
	  		}

            $length = $product->getData($postLength);
            $width  = $product->getData($postWidth);
            $height = $product->getData($postHeight);
            $weight = $product->getData($postWeight);

            // determine if its static or not
            $package_type = "";
            if (stripos($post['package_type'], 'static_pt_') !== false) {
                $package_type = str_replace('static_pt_', '', $post['package_type']);
            } else {
                $package_type = $product->getData($post['package_type']);

                if (is_numeric($package_type)) {
                    $package_type = $product->getAttributeText($post['package_type']);
                }
            }

            if($weightUnit == 'lbs' && !empty($weight)) {
                $weight = floatval($weight) * 0.454;
            }


            // now lets check dimension unit conversion
            if (!empty($post['dimension_unit'])) {
                $length = $length / $post['dimension_unit'];
                $width = $width / $post['dimension_unit'];
                $height = $height / $post['dimension_unit'];
            }

            if (!empty($post['weight_unit'])) {
                $weight = $weight / $post['weight_unit'];
            }


            $existingProductCartonSql = $connection->select('carton_id, package_type')->from($fusedship_product_cartons_table)->where('product_id = :product_id');

            $params = ['product_id' => (int) $id];
            $existing_cartons = $connection->fetchAll($existingProductCartonSql, $params);


            // IMPROVE THIS and do an update at once not by each loop

            if (isset($existing_cartons) && !empty($existing_cartons)) {
                if (isset($post['overwrite'])) {
                    $delete_rows[] = $id;
                    $insert_rows[] = [
                        'product_id'    => $id,
                        'carton_length' => $length,
                        'carton_width'  => $width,
                        'carton_height' => $height,
                        'carton_weight' => $weight,
                        'package_type'  => $package_type
                    ];

                } else {
                    $skipped_products[] = $name;
                }
            } else {
                $insert_rows[] = [
                    'product_id'    => $id,
                    'carton_length' => $length,
                    'carton_width'  => $width,
                    'carton_height' => $height,
                    'carton_weight' => $weight,
                    'package_type'  => $package_type
                ];

            }

            $affected_products[] = $name;
        }

        $this->deleteFusedshipProductCarton($delete_rows);
        $this->insertFusedshipProductCarton($insert_rows);

		if(count($affected_products) > 0){
			return array('affected'=>$affected_products,'skipped'=>$skipped_products,'error'=>$error_products);
		}

        return false;
	}

    private function deleteFusedshipProductCarton($rows) {
        if (empty($rows)) {
            return;
        }

        $conRes  = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\App\ResourceConnection');

        $fsProductCartonTable = $conRes->getTableName('fusedship_product_cartons');

        $deleteSql = "DELETE FROM $fsProductCartonTable WHERE product_id in (" . implode(', ', $rows) .")";

        $connection = $this->_resources->getConnection();
        $connection->query($deleteSql);

        // we should be removing the product data relation aswell
        $fsProductDataTable = $conRes->getTableName('fusedship_product_data');
        $deleteSql = "DELETE FROM $fsProductDataTable WHERE product_id in (" . implode(', ', $rows) .")";
        $connection->query($deleteSql);
    }

    private function insertFusedshipProductCarton($rows) {
        if (empty($rows)) {
            return;
        }

        $conRes  = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\App\ResourceConnection');

        $table = $conRes->getTableName('fusedship_product_cartons');

        $insertSql = "INSERT INTO $table (product_id, carton_length, carton_width, carton_height, carton_weight,package_type) VALUES ";

        $insertRows = [];
        $insertDataRows = [];
        foreach ($rows as $row) {

            $insertRows[] = "('{$row['product_id']}', '{$row['carton_length']}', '{$row['carton_width']}', '{$row['carton_height']}', '{$row['carton_weight']}','{$row['package_type']}')";
            $insertDataRows[] = "({$row['product_id']}, 1)";
        }

        $insertSql .= implode(', ', $insertRows);
        $insertSql .= ';';


        $connection = $this->_resources->getConnection();
        $connection->query($insertSql);

        // we should be inserting the product data relation aswell
        $fsProductDataTable = $conRes->getTableName('fusedship_product_data');

        $insertSql = "INSERT INTO $fsProductDataTable (product_id, use_fusedship_rates) VALUES ";
        $insertSql .= implode(', ', $insertDataRows);
        $connection->query($insertSql);
    }

}

?>
