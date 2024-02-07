<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Module\Manager as ModuleManager;

class Quote extends Field
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ModuleManager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->moduleManager->isEnabled('Amasty_RequestQuote')) {
            $element->setValue(__("Installed"));
            $element->setHtmlId('amasty_is_instaled');
            $element->setComment('');
        } else {
            $element->setValue(__('Not Installed'));
            $element->setHtmlId('amasty_not_instaled');
        }

        return parent::render($element);
    }
}
