<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Plugin\Xlanding\Block\Adminhtml\Page\Edit\Tab;

use Magento\Framework\Data\Form\Element\Fieldset;

class Main
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param Fieldset $fieldset
     * @param $isElementDisabled
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function aroundPrepareStatusField($subject, callable $proceed, Fieldset $fieldset, $isElementDisabled)
    {
        /**
         * @var \Amasty\Xlanding\Model\Page $model
         */
        $model = $this->registry->registry('amasty_xlanding_page');
        if (!$model->isDynamic()){
            return $fieldset->addField(
                $model::LANDING_IS_ACTIVE,
                'select',
                [
                    'label' => __('Status'),
                    'title' => __('Page Status'),
                    'name' => $model::LANDING_IS_ACTIVE,
                    'required' => true,
                    'options' => $model->getAvailableStatuses(),
                    'disabled' => $isElementDisabled
                ]
            );
        }

        return $proceed($fieldset, $isElementDisabled);
    }
}
