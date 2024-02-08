<?php
namespace Biztech\Ausposteparcel\Model\Config\Source;

/**
 * Copyright © 2016 Biztech. All rights reserved.
 * See COPYING.txt for license details.
 */
class Livetest
{
    public function toOptionArray()
    {
        $options = array(
            array('value' => 3, 'label' => __('Sandbox 2 (Latest)')),
            array('value' => 2, 'label' => __('Sandbox 1')),
            array('value' => 1, 'label' => __('Live'))
        );
        return $options;
    }
}
