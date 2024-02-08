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

namespace MageMe\WebForms\Block\Form\Element;

use MageMe\WebForms\Helper\LogicHelper;

class ActionsToolbar extends AbstractElement
{
    protected $_template = self::TEMPLATE_PATH . 'actions-toolbar.phtml';

    /**
     * @return string
     */
    public function getSubmitVisibility(): string
    {
        $_targets = $this->form->_getLogicTarget();
        foreach ($_targets as $target) {
            if ($target['id'] == 'submit') {
                return $target['logic_visibility'];
            }

        }
        return 'visible';
    }

    /**
     * @return string
     */
    public function getSubmitContainerClass(): string
    {
        $classList = ['actions-toolbar'];
        $form = $this->getForm();
        if ($form->getSubmitButtonPosition()) {
            $classList[] = 'submit-' . $form->getSubmitButtonPosition();
        }
        if ($this->getSubmitVisibility() == 'hidden') {
            $classList[] = LogicHelper::HIDDEN_CSS_CLASS;
        }
        return implode(" ", $classList);
    }
}