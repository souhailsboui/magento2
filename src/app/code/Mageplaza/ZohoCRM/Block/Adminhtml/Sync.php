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
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\ZohoCRM\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Mageplaza\ZohoCRM\Helper\Data;

/**
 * Class Sync
 * @package Mageplaza\ZohoCRM\Block\Adminhtml
 */
class Sync extends Template
{
    /**
     * @return string
     */
    public function getSyncUrl()
    {
        return $this->getUrl('mpzoho/queue/sync');
    }

    /**
     * @return string
     */
    public function getEstimateUrl()
    {
        return $this->getUrl('mpzoho/queue/estimatesync');
    }

    /**
     * @return string
     */
    public function getOptions()
    {
        $options = [
            'syncUrl'              => $this->getSyncUrl(),
            'estimateUrl'          => $this->getEstimateUrl(),
            'errorMessage'         => __('Synchronize failed.'),
            'estimateErrorMessage' => __('Estimate synchronize failed.'),
            'successMessage'       => __('A total of #1 record(s) have been synchronized. Note that in case there have already 5 times failed, it is unable to synchronize more. Please edit the object data, save and check again. ')
        ];

        return Data::jsonEncode($options);
    }
}
