<?php

namespace Machship\Fusedship\Block\Adminhtml\Machship;

class Migration extends \Magento\Framework\View\Element\Template
{
	protected $_attributeFactory;
	protected $_fusedshipHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Machship\Fusedship\Helper\Data $_fusedshipHelper,
        array $data = []
    )
    {
        $this->_attributeFactory = $attributeFactory;
        $this->_fusedshipHelper   = $_fusedshipHelper;

        parent::__construct($context, $data);
    }

    public function getAllAttributes()
	{
	    $attribute_data = [];
	    $attributeInfo = $this->_attributeFactory->getCollection()
	                 ->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 4);
	                //  ->addFieldToFilter('is_user_defined', 1);

	   foreach($attributeInfo as $attributes) {
	        $attributeCode = $attributes->getAttributeCode(); // You can get all fields of attribute here

	        $attribute_data[$attributeCode] = $attributes->getFrontendLabel();
	   }

	   return $attribute_data;
	}

    public function getWeightUnit() {
        return $this->_fusedshipHelper->getWeightUnit();
    }


	public function getFormKey()
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$key_form = $objectManager->get('Magento\Framework\Data\Form\FormKey');

		$form_Key = $key_form->getFormKey();
        return $form_Key;
    }



}
