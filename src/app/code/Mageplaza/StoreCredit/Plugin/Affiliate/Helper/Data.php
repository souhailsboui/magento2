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

namespace Mageplaza\StoreCredit\Plugin\Affiliate\Helper;

use Closure;
use Mageplaza\StoreCredit\Helper\Data as StoreCreditHelper;

/**
 * Class Data
 * @package Mageplaza\StoreCredit\Plugin\Affiliate\Helper
 */
class Data
{
    /**
     * @var StoreCreditHelper
     */
    private $helper;

    /**
     * Data constructor.
     *
     * @param StoreCreditHelper $helper
     */
    public function __construct(StoreCreditHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Mageplaza\Affiliate\Helper\Data $subject
     * @param Closure $proceed
     * @param int|null $storeId
     *
     * @return bool
     */
    public function aroundCheckStoreCredit(\Mageplaza\Affiliate\Helper\Data $subject, Closure $proceed, $storeId = null)
    {
        if (!$this->helper->isEnabled($storeId)) {
            return $proceed($storeId);
        }

        $version = $this->helper->getVersion();
        if (version_compare($version, '4.0.4', '>=') ||
            version_compare($version, '2.0.0', '<') && version_compare($version, '1.1.9')
        ) {
            return true;
        }

        return false;
    }
}
