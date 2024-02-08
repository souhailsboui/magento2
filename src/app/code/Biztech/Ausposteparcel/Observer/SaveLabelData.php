<?php
namespace Biztech\Ausposteparcel\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Biztech\Ausposteparcel\Model\Api;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class SaveLabelData implements ObserverInterface
{
    protected $scopeConfig;
    protected $Api;
    protected $messageManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        MessageManagerInterface $messageManager,
        Api $Api
    ) {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->Api = $Api;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $active = (int) $this->scopeConfig->getValue('carriers/ausposteParcel/active', ScopeInterface::SCOPE_STORE);
        if ($active == 1) {
            $response = $this->Api->seteParcelMerchantDetails();
            if ($response['status'] == "error") {
                $this->messageManager->addError($response['message']);
            }
        }
    }
}
