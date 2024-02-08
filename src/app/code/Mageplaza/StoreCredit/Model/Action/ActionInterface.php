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

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ActionInterface
 * @package Mageplaza\StoreCredit\Model\Action
 */
interface ActionInterface
{
    /**
     * Get transaction title
     * @return string
     */
    public function getTitle();

    /**
     * Get transaction action
     * @return string
     */
    public function getAction();

    /**
     * Prepare Transaction data
     * @return array
     * @throws LocalizedException
     */
    public function prepareTransaction();
}
