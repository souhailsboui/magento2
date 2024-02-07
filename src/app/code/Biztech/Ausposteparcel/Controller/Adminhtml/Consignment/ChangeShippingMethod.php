<?php
namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class ChangeShippingMethod extends Action
{
    protected $_resultPageFactory;
    protected $order;
    
    public function __construct(Context $context, PageFactory $resultPageFactory, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->order              = $order;
        parent::__construct($context);
    }
    public function execute()
    {
        $domesticIngoredOrder = [];
        $intlIngoredOrder = [];
        $ingoredOrder = [];
        $successOrder = [];
        $ids = $this->getRequest()->getParam('order_consignment');
        if (!isset($ids)) {
            $this->messageManager->addError(__('Please select item(s)'));
        } else {
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
            foreach ($ids as $id) {
                $values = explode('_', $id);
                $orderId = (int) ($values[0]);

                if ($orderId > 0) {
                    $changeMethod = explode("<->", base64_decode($this->getRequest()->getParam('change_method')));

                    $order = $this->order->load($orderId);
                    $eparcelShippingId = $order->getData('eparcel_shipping_id');
                    if ($eparcelShippingId != null && $eparcelShippingId != '') {
                        $ingoredOrder[] = $order->getIncrementId();
                        continue;
                    }

                    if ($this->order->getShippingAddress()->getCountryId() == "AU") {
                        if (strpos($changeMethod[1], "INT'L") !== false || strpos($changeMethod[1], "INTL") !== false || strpos($changeMethod[1], "APGL") !== false) {
                            $domesticIngoredOrder[] = $order->getIncrementId();
                            continue;
                        }
                    } else {
                        if (strpos($changeMethod[1], "INT") !== 0) {
                            if (strpos($changeMethod[1], "APGL") !== 0) {
                                $intlIngoredOrder[] = $order->getIncrementId();
                                continue;
                            }
                        }
                    }
                    $order->setShippingMethod(trim('ausposteParcel_ausposteParcel-' . $changeMethod[0]));
                    $order->setShippingDescription(trim($changeMethod[1]));
                    $order->save();
                    $successOrder[] = $order->getIncrementId();
                }
            }
        }
        if (!empty($domesticIngoredOrder)) {
            $errorMsg = __('Domestic Order Can not Be Changed With International Shipping Method. #Order %1', implode(',', $domesticIngoredOrder));
            $this->messageManager->addError($errorMsg);
        }
        if (!empty($intlIngoredOrder)) {
            $errorMsg = __('International Order Can not Be Changed With Domestic Shipping Method. #Order %1', implode(',', $intlIngoredOrder));
            $this->messageManager->addError($errorMsg);
        }
        if (!empty($ingoredOrder)) {
            $successmsg = __('Shipping Method Can not Be Changed Because Shipment Already Generated. #Order %1', implode(',', $ingoredOrder));
            $this->messageManager->addError($successmsg);
        }
        if (!empty($successOrder)) {
            $successmsg = __('Successfully Changed Shipping Method For #Order %1', implode(',', $successOrder));
            $this->messageManager->addSuccess($successmsg);
        }
        $this->_redirect('*/*/index');
    }
}
