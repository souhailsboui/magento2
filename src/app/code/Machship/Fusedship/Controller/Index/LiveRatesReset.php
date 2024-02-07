<?php

namespace Machship\Fusedship\Controller\Index;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Api\Data\AddressExtensionInterfaceFactory;

/**
 * Class Index
 * @package Machship\Fusedship\Controller\Index\LiveRatesReset
 */
class LiveRatesReset  implements HttpGetActionInterface
{

    protected $_checkoutSession;
    protected $jsonResultFactory;
    protected $objectManager;

    public function __construct(
        JsonFactory $jsonResultFactory
    ) {
        $this->jsonResultFactory = $jsonResultFactory;

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_checkoutSession = $this->objectManager->get('\Magento\Checkout\Model\Session');
    }

    public function execute()
    {

        $this->_checkoutSession->setFusedshipRates([]);

        $result = $this->jsonResultFactory->create();

        return $result->setData([
            'status' => true,
            'message' => 'resetting cart successful'
        ]);
    }

}