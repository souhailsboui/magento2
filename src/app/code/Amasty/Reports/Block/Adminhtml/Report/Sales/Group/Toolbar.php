<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Block\Adminhtml\Report\Sales\Group;

use Magento\Framework\Data\Form\AbstractForm;

class Toolbar extends \Amasty\Reports\Block\Adminhtml\Report\Toolbar
{
    /**
     * @param AbstractForm $form
     *
     * @return $this
     */
    protected function addControls(AbstractForm $form)
    {
        $this->addDateControls($form);

        $form->addField('value', 'radios', [
            'name'      => 'value',
            'wrapper_class' => 'amreports-filter-interval amreports-filter-switcher',
            'values'    => [
                ['value' => 'quantity', 'label' => __('Quantity')],
                ['value' => 'total', 'label' => __('Total')]
            ],
            'value'     => 'quantity'
        ]);
        
        return parent::addControls($form);
    }
}
