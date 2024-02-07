<?php

namespace Machship\Fusedship\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class CheckoutAddressType implements HttpGetActionInterface
{

    protected $_checkoutSession;
    protected $_objectManager;
    protected $_jsonResultFactory;

    public function __construct(JsonFactory $jsonResultFactory) {
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_checkoutSession = $this->_objectManager->get('\Magento\Checkout\Model\Session');
    }

    public function execute()
    {
        $request = $this->_objectManager->get('Magento\Framework\App\Request\Http');
        $isResidential = filter_var($request->getParam('is_residential'), FILTER_VALIDATE_BOOLEAN);

        $updated = false;

        if ($this->_checkoutSession->getData('fusedship_is_residential') !== $isResidential) {
            $this->_checkoutSession->setData('fusedship_is_residential', $isResidential);
            $updated = true;
        }


        $result = $this->_jsonResultFactory->create();

        return $result->setData([
            'status' => true,
            'message' => 'address type changed successfully',
            'updated' => $updated
        ]);

    }

}