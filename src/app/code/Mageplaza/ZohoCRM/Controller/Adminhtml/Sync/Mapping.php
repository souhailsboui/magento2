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

namespace Mageplaza\ZohoCRM\Controller\Adminhtml\Sync;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Mageplaza\ZohoCRM\Helper\Mapping as HelperMapping;

/**
 * Class Mapping
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Sync
 */
class Mapping extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var HelperMapping
     */
    protected $helperMapping;

    /**
     * Mapping constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param HelperMapping $helperMapping
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        HelperMapping $helperMapping
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->helperMapping     = $helperMapping;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $result['canMapping'] = true;
        $zohoModule           = $this->getRequest()->getParam('zoho_module');
        $magentoObject        = $this->getRequest()->getParam('magento_object');

        $this->_session->unsMpZohoMagentoObject();
        $this->_session->setMpZohoMagentoObject($magentoObject);
        try {
            $html                   = $this->helperMapping->createMappingFields($zohoModule);
            $result['mapping_html'] = $html;
            $result['variables']    = $this->helperMapping->getDefaultVariable($zohoModule, true);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }
}
