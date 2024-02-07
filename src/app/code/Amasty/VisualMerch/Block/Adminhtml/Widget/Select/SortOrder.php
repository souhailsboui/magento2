<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Block\Adminhtml\Widget\Select;

use Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider;

class SortOrder extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Amasty\VisualMerch\Model\Product\Sorting
     */
    private $sorting;

    /**
     * @var AdminhtmlDataProvider
     */
    private $dataProvider;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\VisualMerch\Model\Product\Sorting $sorting,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $dataProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sorting = $sorting;
        $this->dataProvider = $dataProvider;
    }

    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_VisualMerchUi::widget/select.phtml');
    }

    /**
     * @return array
     */
    public function getSelectOptions()
    {
        return $this->sorting->getSortingOptions();
    }

    /**
     * Get current value
     *
     * @return string
     */
    public function getSelectValue()
    {
        return $this->dataProvider->getSortOrder();
    }
}
