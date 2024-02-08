<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Block\Widget;


use Magento\Widget\Block\BlockInterface;

class SlideOutForm extends \MageMe\WebForms\Block\SlideOutForm implements BlockInterface
{
    /**
     * @inheritdoc
     */
    protected function formNotInitializedAction(string $message): string
    {
        $messages = $this->getMessages();
        $messages->addError(__('Form not found.'));
        return $messages->getGroupedHtml();
    }
}
