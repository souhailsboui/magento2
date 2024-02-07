<?php

namespace Biztech\Ausposteparcel\Model\Config\Source;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Classificationtype extends \Magento\Framework\Data\Collection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
    ) {
        parent::__construct(
            $entityFactory
        );
    }

    public function toOptionArray()
    {
        $classificationTypes = array('GIFT' => "Gift", 'SAMPLE' => "Sample", 'DOCUMENT' => "Document", 'RETURN' => "Return");
        foreach ($classificationTypes as $key => $classificationType) {
            $classification[] = array('label' => $classificationType, 'value' => $key);
        }

        return $classification;
    }
}
