<?php

namespace Meetanshi\ShippingRules\Plugin\Quote\Model\Quote\Address;

use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateResult\AbstractResult;

class ImportRate
{
    public function aroundImportShippingRate(Rate $subject, \Closure $closure, AbstractResult $rate)
    {
        $data = $closure($rate);
        $data->setOldPrice($rate->getOldPrice());
        return $data;
    }
}
