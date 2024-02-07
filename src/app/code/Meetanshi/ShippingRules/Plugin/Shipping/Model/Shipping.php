<?php

namespace Meetanshi\ShippingRules\Plugin\Shipping\Model;

use Meetanshi\ShippingRules\Model\Validator;
use Magento\Shipping\Model\Shipping as ShippingModel;
use Magento\Quote\Model\Quote\Address\RateRequest;

class Shipping
{
    protected $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function aroundCollectRates(ShippingModel $subject, \Closure $closure, RateRequest $request)
    {
        $closure($request);

        $result = $subject->getResult();
        $rates = $result->getAllRates();
        $prices = [];
        foreach ($rates as $rate) {
            $prices[$rate->getMethod()] = $rate->getPrice();
        }

        $newShippingRates = [];

        $this->validator->init($request);
        if (!$this->validator->validateRules($rates)) {
            return $subject;
        }

        $this->validator->applyShippingRules($rates);
        foreach ($rates as $rate) {
            if ($this->validator->needNewRequest($rate)) {
                $newShippingRequest = $this->validator->getNewRequest($rate);
                if (count($newShippingRequest->getAllItems())) {
                    $result->reset();
                    $closure($newShippingRequest);
                    $rate = $this->validator->findRate($result->getAllRates(), $rate);
                } else {
                    $rate->setPrice(0);
                }
            }
            $rate->setPrice($rate->getPrice() + $this->validator->getFee($rate));
            $newShippingRates[] = $rate;
        }

        $result->reset();
        foreach ($newShippingRates as $rate) {
            $rate->setOldPrice($prices[$rate->getMethod()]);
            $rate->setPrice(max(0, $rate->getPrice()));
            $result->append($rate);
        }
        return $subject;
    }
}
