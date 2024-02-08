<?php

namespace Meetanshi\ShippingRules\Model;

use Magento\Rule\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DataObject;

class Rule extends AbstractModel
{
    const REPLACE = 0;
    const ADD = 1;
    const DEDUCT = 2;
    protected $objectManager;
    protected $storeManager;

    public function __construct(Context $context, Registry $registry, FormFactory $formFactory, TimezoneInterface $localeDate, StoreManagerInterface $storeManager, ObjectManagerInterface $objectManager, array $data = [])
    {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory, $localeDate, null, null, $data);
    }

    public function validate(DataObject $object)
    {
        return $this->getConditions()->validateNotModel($object);
    }

    public function getConditionsInstance()
    {
        return $this->objectManager->create('Meetanshi\ShippingRules\Model\Rule\Condition\Combine');
    }

    public function getActionsInstance()
    {
        return $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine');
    }

    public function massChangeStatus($ids, $status)
    {
        return $this->getResource()->massChangeStatus($ids, $status);
    }

    public function loadPost(array $rule)
    {
        $array = $this->_convertFlatToRecursive($rule);
        if (isset($array['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray($array['conditions'][1]);
        }
        if (isset($array['actions'])) {
            $this->getActions()->setActions([])->loadArray($array['actions'][1], 'actions');
        }
        return $this;
    }

    public function ruleMatch($rate)
    {
        $carriers = explode(',', $this->getShippingCarriers());

        if (in_array($rate->getCarrier(), $carriers)) {
            return true;
        }
        $methods = $this->getShippingMethods();

        if (!$methods) {
            return false;
        }
        $methods = array_unique(explode(',', $methods));
        $carrierCode = $rate->getCarrier() . '_' . $rate->getMethod();

        foreach ($methods as $name) {
            if ($carrierCode == $name) {
                return true;
            }
        }

        return false;
    }

    public function validateTotals($totals)
    {
        $keys = ['price', 'qty', 'weight'];
        foreach ($keys as $k) {
            $v = $totals['not_free_' . $k];
            if ($this->getData($k . '_from') > 0 && $v < $this->getData($k . '_from')) {
                return false;
            }

            if ($this->getData($k . '_to') > 0 && $v > $this->getData($k . '_to')) {
                return false;
            }
        }

        return true;
    }

    public function calculateRate($totals, $isFree)
    {
        if ($isFree) {
            $this->setFee(0);
            return 0;
        }

        $rate = 0;

        $qty = $totals['not_free_qty'];
        $weight = $totals['not_free_weight'];
        $price = $totals['not_free_price'];

        if ($qty > 0) {
            $rate += $this->getRateBase();
        }

        $rate += $qty * $this->getRateFixed();
        $rate += $price * $this->getRatePercent() / 100;
        $rate += $weight * $this->getWeightFixed();

        if ($this->getCalculation() == self::DEDUCT) {
            $rate = 0 - $rate;
        }

        $this->setFee($rate);

        return $rate;
    }

    public function removeFromRequest()
    {
        return ($this->getCalculation() == self::REPLACE);
    }

    public function afterSave()
    {
        $ruleProductAttributes = array_merge(
            $this->_getUsedAttributes($this->getConditionsSerialized()),
            $this->_getUsedAttributes($this->getActionsSerialized())
        );
        if (count($ruleProductAttributes)) {
            $this->getResource()->saveAttributes($this->getId(), $ruleProductAttributes);
        }

        return parent::afterSave();
    }

    protected function _getUsedAttributes($string)
    {
        $result = [];
        $pattern = '~s:46:"Magento\\\SalesRule\\\Model\\\Rule\\\Condition\\\Product";s:9:"attribute";s:\d+:"(.*?)"~s';
        $matches = [];
        if (preg_match_all($pattern, $string, $matches)) {
            foreach ($matches[1] as $attributeCode) {
                $result[] = $attributeCode;
            }
        }

        return $result;
    }

    public function beforeSave()
    {
        $this->_setWebsiteIds();
        return parent::beforeSave();
    }

    protected function _setWebsiteIds()
    {
        $websites = [];

        foreach ($this->storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $websites[$website->getId()] = $website->getId();
                }
            }
        }

        $this->setOrigData('website_ids', $websites);
    }

    public function beforeDelete()
    {
        $this->_setWebsiteIds();
        return parent::beforeDelete();
    }

    protected function _construct()
    {
        $this->_init('Meetanshi\ShippingRules\Model\ResourceModel\Rule');
        parent::_construct();
    }
}
