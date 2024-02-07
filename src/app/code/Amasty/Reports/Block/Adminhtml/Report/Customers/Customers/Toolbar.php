<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Block\Adminhtml\Report\Customers\Customers;

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

        $form->addField('interval', 'radios', [
            'name'      => 'interval',
            'wrapper_class' => 'amreports-filter-interval',
            'values'    => [
                ['value' => 'day', 'label' => __('Day')],
                ['value' => 'week', 'label' => __('Week')],
                ['value' => 'month', 'label' => __('Month')],
                ['value' => 'year', 'label' => __('Year')],
            ],
            'value'     => 'day'
        ]);

        $this->addViewControls(
            $form,
            [
                ['value' => 'multi-linear', 'label' => __('Multi-linear')],
                ['value' => 'multi-column', 'label' => __('Multi-column')]
            ],
            'multi-linear'
        );
        
        parent::addControls($form);
        return $this;
    }
}
