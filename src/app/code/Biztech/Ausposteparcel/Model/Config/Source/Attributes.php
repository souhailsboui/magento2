<?php
namespace Biztech\Ausposteparcel\Model\Config\Source;

use Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Option\ArrayInterface;

class Attributes implements ArrayInterface
{
    protected $objectManager;

    public function __construct(ObjectManagerInterface $interface)
    {
        $this->objectManager = $interface;
    }

    public function toOptionArray()
    {
        $customerAttributes = $this->objectManager->get('Magento\Catalog\Model\Product')->getAttributes();
        $attributesArrays = [];
        foreach ($customerAttributes as $cal => $val) {
            $attributesArrays[] = [
                'label' => $cal,
                'value' => $cal
            ];
        }
        return $attributesArrays;
    }
}
