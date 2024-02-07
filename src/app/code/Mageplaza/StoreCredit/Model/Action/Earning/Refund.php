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

namespace Mageplaza\StoreCredit\Model\Action\Earning;

use Mageplaza\StoreCredit\Model\Action\Earning;

/**
 * Class Refund
 * @package Mageplaza\StoreCredit\Model\Action\Earning
 */
class Refund extends Earning
{
    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'Take back credit spent on refunded order #%1';
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return __('Taken back for refunding Order');
    }
}
