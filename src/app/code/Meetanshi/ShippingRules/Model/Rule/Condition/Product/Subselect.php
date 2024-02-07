<?php

namespace Meetanshi\ShippingRules\Model\Rule\Condition\Product;

use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Subselect as ProductSubSelect;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;
use Magento\Framework\Model\AbstractModel;

class Subselect extends ProductSubSelect
{
    public function __construct(Context $context, Product $ruleConditionProduct, array $data = [])
    {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType('Meetanshi\ShippingRules\Model\Rule\Condition\Product\Subselect')->setValue(null);
    }

    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'qty' => __('total quantity'),
                'base_row_total' => __('total amount excl. tax'),
                'base_row_total_incl_tax' => __('total amount incl. tax'),
                'row_weight' => __('total weight'),
            ]
        );
        return $this;
    }

    public function validate(AbstractModel $object)
    {
        return $this->validateNotModel($object);
    }

    public function validateNotModel($object)
    {
        $attributes = $this->getAttribute();
        $total = 0;
        if ($object->getAllItems()) {
            $validIds = [];
            foreach ($object->getAllItems() as $item) {
                if ($item->getProduct()->getTypeId() == 'configurable') {
                    $item->getProduct()->setTypeId('skip');
                }
                if (Combine::validate($item)) {
                    $itemParentId = $item->getParentItemId();
                    if (is_null($itemParentId)) {
                        $validIds[] = $item->getItemId();
                    } else {
                        if (in_array($itemParentId, $validIds)) {
                            continue;
                        } else {
                            $validIds[] = $itemParentId;
                        }
                    }
                    $total += $item->getData($attributes);
                }

                if ($item->getProduct()->getTypeId() === 'skip') {
                    $item->getProduct()->setTypeId('configurable');
                }
            }
        }

        return $this->validateAttribute($total);
    }
}
