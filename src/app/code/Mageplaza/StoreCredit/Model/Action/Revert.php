<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_StoreCredit
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\StoreCredit\Model\Action;

use Mageplaza\StoreCredit\Model\Action;

/**
 * Class Revert
 * @package Mageplaza\StoreCredit\Model\Action
 */
class Revert extends Action
{
    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'Reverted from order #%1';
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return __('Reverted');
    }
}
