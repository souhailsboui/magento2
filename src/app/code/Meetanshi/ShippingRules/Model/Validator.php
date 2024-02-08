<?php

namespace Meetanshi\ShippingRules\Model;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State;
use Magento\Backend\App\Area\FrontNameResolver;

class Validator extends DataObject
{
    protected $rateAdjustments = [];

    protected $objectManager;

    private $appState;

    public function __construct(ObjectManagerInterface $objectManager, State $appState, array $data = [])
    {
        $this->objectManager = $objectManager;
        $this->appState = $appState;
        parent::__construct($data);
    }

    public function init($request)
    {
        $this->setRequest($request);
        return $this;
    }

    public function applyShippingRules($rates)
    {
        $request = $this->getRequest();

        $ids = [];

        foreach ($rates as $rate) {
            $this->rateAdjustments[$this->getKey($rate)] = [
                'fee' => 0,
                'totals' => $this->initTotals(),
                'ids' => [],
            ];
            $ids[$this->getKey($rate)] = [];
        }

        foreach ($this->getShippingRules() as $rule) {
            $rule->setFee(0);

            $group = [];
            foreach ($request->getAllItems() as $item) {
                if (!($rule->getActions()->validate($item))) {
                    continue;
                }
                $group[$item->getItemId()] = $item;
            }

            if (!$group) {
                continue;
            }

            $subTotals = $this->calculateTotals($group, $request->getFreeShipping());
            if ($rule->validateTotals($subTotals)) {
                $rule->calculateRate($subTotals, $request->getFreeShipping());

                foreach ($rates as $rate) {
                    $currentIds = array_keys($group);
                    $oldIds = $ids[$this->getKey($rate)];
                    if ($rule->ruleMatch($rate) && !count(array_intersect($currentIds, $oldIds))) {
                        $ids[$this->getKey($rate)] = array_merge($currentIds, $oldIds);

                        $a = $this->rateAdjustments[$this->getKey($rate)];
                        $a['fee'] += $rule->getFee();


                        $handling = $rule->getHandlingFee();
                        if (is_numeric($handling)) {
                            if ($rule->getCalculation() == Rule::DEDUCT) {
                                $a['fee'] -= $rate->getPrice() * $handling / 100;
                            } else {
                                $a['fee'] += $rate->getPrice() * $handling / 100;
                            }
                        }

                        if ($rule->removeFromRequest()) {
                            foreach ($subTotals as $k => $value) {
                                if (isset($a['totals'][$k])) {
                                    $a['totals'][$k] += $value;
                                }
                            }
                            $a['ids'] = array_merge($a['ids'], array_keys($group));
                        }

                        $this->rateAdjustments[$this->getKey($rate)] = $a;
                    }
                }
            }
        }
        return $this;
    }

    public function getKey($rate)
    {
        return $rate->getCarrier() . '~' . $rate->getMethod();
    }

    public function initTotals()
    {
        $totals = [
            'price' => 0,
            'not_free_price' => 0,
            'weight' => 0,
            'not_free_weight' => 0,
            'qty' => 0,
            'not_free_qty' => 0,
        ];
        return $totals;
    }

    public function getShippingRules()
    {
        $request = $this->getRequest();

        $hash = $this->getAddressOptions($request);
        if ($this->getData('rules_by_' . $hash)) {
            return $this->getData('rules_by_' . $hash);
        }

        $validRules = [];
        foreach ($this->getAllShippingRules() as $rule) {
            $rule->afterLoad();
            if ($rule->validate($request)) {
                $validRules[] = $rule;
            }
        }

        $this->setData('rule_by_' . $hash, $validRules);

        return $validRules;
    }

    public function getAddressOptions($request)
    {
        $addressCondition = $this->objectManager->create('Meetanshi\ShippingRules\Model\Rule\Condition\Address');
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();

        $hash = '';
        foreach ($addressAttributes as $code => $label) {
            $hash .= $request->getData($code) . $label;
        }

        return hash('SHA512', $hash);
    }

    public function getAllShippingRules()
    {
        $request = $this->getRequest();
        if (!$this->getData('rules_all')) {
            $collection = $this->objectManager->create('Meetanshi\ShippingRules\Model\Rule')
                ->getCollection()
                ->isActive()
                ->storeFilter($request->getStoreId())
                ->customerGroupFilter($this->getCustomerGroupId())
                ->daysFilter()
                ->setOrder('position', 'asc');

            if ($this->appState->getAreaCode() == FrontNameResolver::AREA_CODE) {
                $collection->addFieldToFilter('is_admin', 1);
            }
            $collection->load();

            $this->setData('rules_all', $collection);
        }

        return $this->getData('rules_all');
    }

    public function getCustomerGroupId()
    {
        $request = $this->getRequest();
        $customerGroupId = 0;

        $items = current($request->getAllItems());
        if ($items->getQuote()->getCustomerId()) {
            $customerGroupId = $items->getQuote()->getCustomer()->getGroupId();
        }

        return $customerGroupId;
    }

    public function calculateTotals($group, $isFree)
    {
        $totals = $this->initTotals();

        foreach ($group as $item) {
            if ($item->getParentItem() || $item->getProduct()->isVirtual()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $children) {
                    if ($children->getProduct()->isVirtual()) {
                        continue;
                    }

                    $qty = $item->getQty() * $children->getQty();
                    $notFree = $item->getQty() * ($qty - $this->getFreeQty($children));

                    $totals['qty'] += $qty;
                    $totals['not_free_qty'] += $notFree;

                    $totals['price'] += $children->getBaseRowTotal();
                    $totals['not_free_price'] += $children->getBasePrice() * $notFree;

                    if (!$item->getProduct()->getWeightType()) {
                        $totals['weight'] += $children->getWeight() * $qty;
                        $totals['not_free_weight'] += $children->getWeight() * $notFree;
                    }
                }
                if ($item->getProduct()->getWeightType()) {
                    $totals['weight'] += $item->getWeight() * $item->getQty();
                    $totals['not_free_weight'] += $item->getWeight() * ($item->getQty() - $this->getFreeQty($item));
                }
            } else {
                $qty = $item->getQty();
                $notFree = ($qty - $this->getFreeQty($item));

                $totals['qty'] += $qty;
                $totals['not_free_qty'] += $notFree;

                $totals['price'] += $item->getBaseRowTotal();
                $totals['not_free_price'] += $item->getBasePrice() * $notFree;

                $totals['weight'] += $item->getWeight() * $qty;
                $totals['not_free_weight'] += $item->getWeight() * $notFree;
            }
        }

        if ($isFree) {
            $totals['not_free_price'] = $totals['not_free_weight'] = $totals['not_free_qty'] = 0;
        }

        return $totals;
    }

    public function getFreeQty($item)
    {
        $freeQty = 0;
        if ($item->getFreeShipping()) {
            $freeQty = (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : $item->getQty());
        }
        return $freeQty;
    }

    public function needNewRequest($rate)
    {
        $k = $this->getKey($rate);
        if (empty($this->rateAdjustments[$k])) {
            return false;
        }

        return (count($this->rateAdjustments[$k]['ids']));
    }

    public function getNewRequest($rate)
    {
        $a = $this->rateAdjustments[$this->getKey($rate)];

        $totalsToDeduct = $a['totals'];
        $idsToRemove = $a['ids'];

        $request = clone $this->getRequest();

        $newItems = [];
        foreach ($request->getAllItems() as $item) {
            $id = $item->getItemId();
            if (in_array($id, $idsToRemove)) {
                continue;
            }
            $newItems[] = $item;
        }
        $request->setAllItems($newItems);

        $request->setPackageValue($request->getPackageValue() - $totalsToDeduct['price']);
        $request->setPackageWeight($request->getPackageWeight() - $totalsToDeduct['weight']);
        $request->setPackageQty($request->getPackageQty() - $totalsToDeduct['qty']);
        $request->setFreeMethodWeight($request->getFreeMethodWeight() - $totalsToDeduct['not_free_weight']);
        $request->setPackageValueWithDiscount($request->getPackageValue());
        $request->setPackagePhysicalValue($request->getPackageValue());

        return $request;
    }

    public function validateRules($rates)
    {
        $request = $this->getRequest();

        if (!count($request->getAllItems())) {
            return false;
        }

        $items = current($request->getAllItems());
        if ($items->getQuote()->isVirtual()) {
            return false;
        }

        $rules = $this->getAllShippingRules();
        foreach ($rules as $rule) {
            foreach ($rates as $rate) {
                if ($rule->ruleMatch($rate)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function findRate($rates, $rate)
    {
        foreach ($rates as $r) {
            if ($this->getKey($r) == $this->getKey($rate)) {
                return $r;
            }
        }

        return $rate;
    }

    public function getFee($rate)
    {
        $k = $this->getKey($rate);
        if (empty($this->rateAdjustments[$k])) {
            return 0;
        }

        return $this->rateAdjustments[$k]['fee'];
    }
}
