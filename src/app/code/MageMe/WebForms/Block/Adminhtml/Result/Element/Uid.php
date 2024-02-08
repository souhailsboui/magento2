<?php

namespace MageMe\WebForms\Block\Adminhtml\Result\Element;

use Magento\Framework\Data\Form\Element\Hidden;

class Uid extends Hidden
{
    /**
     *
     */
    const TYPE = 'uid';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->_escaper->escapeHtml($this->getData('name'));
    }
}