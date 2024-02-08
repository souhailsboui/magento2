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
namespace Mageplaza\ZohoCRM\Ui\Component\Control;

use Magento\Ui\Component\Control\Action;

/**
 * Class InvoiceAction
 * @package Mageplaza\ZohoCRM\Ui\Component\Control
 */
class InvoiceAction extends Action
{
    /**
     * Prepare
     *
     * @return void
     */
    public function prepare()
    {
        $config  = $this->getConfiguration();
        $context = $this->getContext();

        $config['url'] = $context->getUrl(
            'mpzoho/invoice/massAction',
            ['order_id' => $context->getRequestParam('order_id')]
        );
        $this->setData('config', $config);

        parent::prepare();
    }
}
