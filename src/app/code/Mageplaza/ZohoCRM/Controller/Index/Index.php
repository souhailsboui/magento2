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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Index
 * @package Mageplaza\ZohoCRM\Controller\Index
 */
class Index extends Token
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($this->session->getMpZohoErrorMessage()) {
            printf('<b style="color:red">' . $this->session->getMpZohoErrorMessage()->getText() . '</b>');
            $this->session->setMpZohoErrorMessage('');
        }

        if ($this->session->getMpZohoSuccessMessage()) {
            printf('<b style="color:green">' . $this->session->getMpZohoSuccessMessage()->getText() . '</b>');
            $this->session->setMpZohoSuccessMessage('');
        }
    }
}
