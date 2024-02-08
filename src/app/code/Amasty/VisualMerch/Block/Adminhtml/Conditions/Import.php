<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Block\Adminhtml\Conditions;

class Import extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setTemplate('Amasty_VisualMerch::conditions/import.phtml');
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getImportConditionSelect()
    {
        $block = $this->getLayout()->createBlock(
            \Amasty\VisualMerch\Block\Adminhtml\Widget\Select\Categories::class,
            'categories-select'
        );
        $block->setId('am-categories-select')
            ->setLabel(__('Import conditions from'));
        return $block;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getImportConditionsButton()
    {
        $block = $this->getLayout()
            ->createBlock( \Magento\Backend\Block\Widget\Button::class, 'import-conditions-button');
        $block->setId('am-import-conditions')
            ->setLabel(__('Import'))
            ->setClass('secondary sort-products');
        return $block;
    }
}
