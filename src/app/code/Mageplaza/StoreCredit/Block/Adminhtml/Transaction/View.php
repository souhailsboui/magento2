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

namespace Mageplaza\StoreCredit\Block\Adminhtml\Transaction;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Mageplaza\StoreCredit\Model\Transaction;

/**
 * Class View
 * @package Mageplaza\StoreCredit\Block\Adminhtml\Transaction
 */
class View extends Container
{
    /**
     * Core registry
     * @var Registry
     */
    public $_coreRegistry;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_mode = 'view';
        $this->_blockGroup = 'Mageplaza_StoreCredit';
        $this->_controller = 'adminhtml_transaction';

        parent::_construct();

        $transaction = $this->getTransaction();

        if ($transaction->getTransactionId()) {
            $this->removeButton('save');
            $this->removeButton('reset');
            $this->removeButton('delete');
        } else {
            $this->addButton(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue View'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'saveAndContinueEdit',
                                'target' => '#edit_form'
                            ]
                        ]
                    ]
                ],
                -100
            );
        }
    }

    /**
     * Get confirm set location
     *
     * @param $action
     *
     * @return string
     */
    public function getConfirmSetLocation($action)
    {
        $message = __('This action can not be restored. Are you sure?');
        $url = $this->getUrl('*/*/' . $action, ['id' => $this->getTransaction()->getId()]);

        return "confirmSetLocation('{$message}', '{$url}')";
    }

    /**
     * Get Transaction
     *
     * @return Transaction|null
     */
    public function getTransaction()
    {
        return $this->_coreRegistry->registry('transaction');
    }
}
