<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Order;

class AddressSave extends \Magento\Sales\Controller\Adminhtml\Order\AddressSave
{
    protected $_encryptor;
    protected $scopeConfig;
    protected $regionCollection;
    protected $url;
    protected $order;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\RegionFactory $regionCollection
    ) {
        $this->_encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->regionCollection = $regionCollection;
        $this->order = $order;
        parent::__construct($context, $coreRegistry, $fileFactory, $translateInline, $resultPageFactory, $resultJsonFactory, $resultLayoutFactory, $resultRawFactory, $orderManagement, $orderRepository, $logger);
    }

    public function execute()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        /** @var $address \Magento\Sales\Api\Data\OrderAddressInterface|\Magento\Sales\Model\Order\Address */
        $address = $this->_objectManager->create('Magento\Sales\Api\Data\OrderAddressInterface')->load($addressId);
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        /*If Auspost shipping method with incorrect address saving then giving error, address should not be change. | 15-4-2021 by JH*/
        $order = $this->order->load($address->getParentId());
        $shipping_method = $order->getShippingDescription();
        $auspostDomesticOrder = false;
        if (strpos($shipping_method, 'INTL') === false  && strpos($shipping_method, 'INT') === false && strpos($shipping_method, "APGL") === false) {
            $auspostDomesticOrder = true;
        }
        $shipping_service = $order->getShippingMethod();
        if($auspostDomesticOrder==true && $data['country_id'] != 'AU' && strpos($shipping_service, 'ausposteParcel') !== false) {
            $this->messageManager->addError("Order placed with the domestic Auspost shipping method, so address can not be changed with international country.");
            return $resultRedirect->setPath('sales/*/address', ['address_id' => $address->getId()]);
        }
        if($auspostDomesticOrder==false && $data['country_id'] == 'AU' && strpos($shipping_service, 'ausposteParcel') !== false) {
            $this->messageManager->addError("Order placed with the international Auspost shipping method, so address can not be changed with australia country.");
            return $resultRedirect->setPath('sales/*/address', ['address_id' => $address->getId()]);
        }
        /*End*/
        if ($data && $address->getId()) {
            if ($data['country_id'] == 'AU') {
                $Accountnumber = $this->_encryptor->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/accountNo', $storeScope));
                $userName = $this->_encryptor->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/apiKey', $storeScope));
                $password = $this->_encryptor->decrypt($this->scopeConfig->getValue('carriers/ausposteParcel/password', $storeScope));

                $suburb = $data['city'];
                $regionId = $data['region_id'];
                $region = $this->regionCollection->create()->load($regionId);
                $state = $region->getCode();
                $postcode = $data['postcode'];

                if ($this->scopeConfig->getValue('carriers/ausposteParcel/operationMode', $storeScope) == 3) {
                    $this->url = 'https://digitalapi.auspost.com.au/test/shipping/v1/address?suburb=' . urlencode($suburb) . '&state=' . $state . '&postcode=' . $postcode;
                } elseif ($this->scopeConfig->getValue('carriers/ausposteParcel/operationMode', $storeScope) == 1) {
                    $this->url = 'https://digitalapi.auspost.com.au/shipping/v1/address?suburb=' . urlencode($suburb) . '&state=' . $state . '&postcode=' . $postcode;
                }
                
                $ch = curl_init($this->url);
                curl_setopt($ch, CURLOPT_USERPWD, $userName . ":" . $password);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Account-Number: ' . $Accountnumber));
                $result = curl_exec($ch);
                
                $responseData = json_decode($result);

                if (isset($responseData->errors[0]->code) && $responseData->errors[0]->code) {
                    $this->messageManager->addError(__($responseData->errors[0]->message));
                    return $resultRedirect->setPath('sales/*/address', ['address_id' => $address->getId()]);
                }
                
                if ($responseData->found && sizeof($responseData->results) && array_map('strtolower', $responseData->results)) {
                    $address->addData($data);
                    try {
                        $address->save();
                        $order->setIsAddressValid(1);
                        $order->save();

                        $this->_eventManager->dispatch(
                            'admin_sales_order_address_update',
                            [
                            'order_id' => $address->getParentId()
                                ]
                        );
                        $this->messageManager->addSuccess(__('You updated the order address.'));
                        return $resultRedirect->setPath('sales/*/view', ['order_id' => $address->getParentId()]);
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\Exception $e) {
                        $this->messageManager->addException($e, __('We can\'t update the order address right now.'));
                    }
                    return $resultRedirect->setPath('sales/*/address', ['address_id' => $address->getId()]);
                } elseif (!$responseData->found && sizeof($responseData->results)) {
                    $suggesstions = implode(', ', $responseData->results);
                    $this->messageManager->addError('Please enter valid City / Suburb for this address. Suggestions are : ' . $suggesstions);
                    return $resultRedirect->setPath('sales/*/address', ['address_id' => $address->getId()]);
                } else {
                    $this->messageManager->addError("Provided combination of Suburb, State & Postcode doesn't match. Please review it and try again.");
                    return $resultRedirect->setPath('sales/*/address', ['address_id' => $address->getId()]);
                }
            } else {
                $address->addData($data);
                try {
                    $address->save();
                    $this->_eventManager->dispatch(
                        'admin_sales_order_address_update',
                        [
                        'order_id' => $address->getParentId()
                            ]
                    );
                    $this->messageManager->addSuccess(__('You updated the order address.'));
                    return $resultRedirect->setPath('sales/*/view', ['order_id' => $address->getParentId()]);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('We can\'t update the order address right now.'));
                }
                return $resultRedirect->setPath('sales/*/address', ['address_id' => $address->getId()]);
            }
        } else {
            return $resultRedirect->setPath('sales/*/');
        }
    }
}
