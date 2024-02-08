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

namespace MageMe\WebFormsZoho\Controller\Adminhtml\System;

use Exception;
use MageMe\WebFormsZoho\Helper\ZohoHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class CrmScopeCheck extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::settings';
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var ZohoHelper
     */
    private $zohoHelper;

    /**
     * @param ZohoHelper $zohoHelper
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        ZohoHelper  $zohoHelper,
        JsonFactory $resultJsonFactory,
        Context     $context
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->zohoHelper = $zohoHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'token' => '',
            'message' => '',
        ];

        try {
            $success = !empty($this->zohoHelper->getApi()->CRM()->getLeadFields());
            if (!$success) {
                throw new Exception(__('Something went wrong.'));
            }
            $result['message'] = __('Available');
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}