<?php
namespace Biztech\Ausposteparcel\Model\Config\Source;

use Biztech\Ausposteparcel\Model\Auspostlabel;
use Magento\Framework\Option\ArrayInterface;

class Contractproducttype implements ArrayInterface
{
    protected $auspostlabel;

    public function __construct(
        Auspostlabel $auspostlabel
    ) {
        $this->auspostlabel = $auspostlabel;
    }

    public function toOptionArray()
    {
        $shippingmethod = array();


        $auspostlabel = $this->auspostlabel->getCollection();
        
        if (sizeof($auspostlabel) > 0) {
            foreach ($auspostlabel as $item) {
                $shippingmethod[] = array('value' => $item->getChargeCode(), 'label'=>$item->getType());
            }
        }
        return $shippingmethod;
    }
}
