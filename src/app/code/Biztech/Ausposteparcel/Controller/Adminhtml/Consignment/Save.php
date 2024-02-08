<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Biztech\Ausposteparcel\Model\Consignment;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @property messageManager
 */
class Save extends Action
{
    protected $_consignment;
    protected $order;
    protected $article;
    protected $info;
    protected $scopeconfiginterface;
    protected $consignmentmodelFactory;
    protected $manifestmodelFactory;
    protected $datahelper;
    protected $apimodel;

    // protected $messageManager;

    public function __construct(
        Context $context,
        Consignment $consignment,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Helper\Article $article,
        \Biztech\Ausposteparcel\Helper\Info $info,
        ScopeConfigInterface $scopeconfiginterface,
        \Biztech\Ausposteparcel\Model\Cresource\Consignment\CollectionFactory $consignmentmodelFactory,
        \Biztech\Ausposteparcel\Model\Cresource\Manifest\CollectionFactory $manifestmodelFactory,
        \Biztech\Ausposteparcel\Helper\Data $datahelper,
        \Biztech\Ausposteparcel\Model\Api $apimodel
    ) {
        parent::__construct($context);
        $this->_consignment = $consignment;
        $this->scopeconfiginterface = $scopeconfiginterface;
        $this->order = $order;
        $this->info = $info;
        $this->article = $article;
        $this->consignmentmodelFactory = $consignmentmodelFactory;
        $this->manifestmodelFactory = $manifestmodelFactory;
        $this->datahelper = $datahelper;
        $this->apimodel = $apimodel;
        $this->messageManager = $context->getMessageManager();
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $order_id = $this->getRequest()->getParam('order_id');
        $order = $this->order->load($order_id);
        $product_id = explode('-', $order->getShippingMethod());
        
        $number_of_articles = (int) trim($this->getRequest()->getParam('number_of_articles'));
        $data = $this->getRequest()->getParams();

        $tempCanConsignments = (int) ($number_of_articles / 20);
        $canConsignments = $tempCanConsignments;
        $remainArticles = $number_of_articles % 20;
        if ($remainArticles > 0) {
            $canConsignments++;
        }
        $consignmentNumber = "";
        for ($i = 0; $i < $canConsignments; $i++) {
            $data['start_index'] = ($i * 20) + 1;
            if (($i + 1) <= $tempCanConsignments) {
                $data['end_index'] = ($i * 20) + 20;
            } else {
                $data['end_index'] = ($i * 20) + $remainArticles;
            }
            try {
                $articleData = $this->article->prepareArticleData($data, $order);
                
                $orderWeight = $this->info->getOrderWeight($order);
                $total_weight = $articleData['total_weight'];

                $content = $articleData['content'];

                $chargeCode = $articleData['charge_code'];
                $merchant_location_id = $this->scopeconfiginterface->getValue('carriers/ausposteParcel/merchantLocationId');

                $consignment_collection = $this->consignmentmodelFactory->create();
                $consignment_collection = $consignment_collection->getLastItem();
                $oldConsignmentNumber = $consignment_collection['consignment_number'];
                if ($oldConsignmentNumber) {
                    $oldConsignmentId = str_replace($merchant_location_id!=null?$merchant_location_id:'', '', $oldConsignmentNumber!=null?$oldConsignmentNumber:'');
                    $newConsignmentInc = (intval($oldConsignmentId) + 1);
                    $consignmentId = str_pad($newConsignmentInc, 7, "0", STR_PAD_LEFT);
                    $consignmentNumber = $merchant_location_id . $consignmentId;
                } else {
                    $consignmentNumber = $merchant_location_id . '0000001';
                }

                $manifestNumber = '';
                  
                if (isset($data['cash_to_collect']) && $data['cash_to_collect']) {
                    $cash_to_collect = 1;
                } else {
                    $cash_to_collect = 0;
                }

                $articleNumbers = array();
                for ($j = 0; $j < $number_of_articles; $j++) {
                    $articleNumbers[$j] = '';
                    /*$articleNumbers[$j] .= $consignmentNumber;*/
                    if (strlen($j) > 1) {
                        $articleNumbers[$j] .= strval($j + 1);
                    } else {
                        $articleNumbers[$j] .= '0' . strval($j + 1);
                    }
                    $chargeCode = $product_id[1];

                    $articleNumbers[$j] .= $this->info->getServiceCode($chargeCode, $data['delivery_signature_allowed'], $cash_to_collect, $data['print_return_labels'], $data['partial_delivery_allowed']);

                    if ($j == ($number_of_articles - 1)) {
                        $articleNumbers[$j] .= '2';
                    } else {
                        $articleNumbers[$j] .= '1';
                    }

                    $articleNumbers[$j] .= '6';
                    $articleNumbers[$j] .= '0';
                    $articleId = str_pad(($j + 1), 4, "0", STR_PAD_LEFT);
                    $articleNumbers[$j] .= $articleId;
                }

                $total_weight = $articleData['total_weight'];
                if ($consignmentNumber) {
                    $this->datahelper->insertConsignment($order_id, $consignmentNumber, $data, $manifestNumber, $chargeCode, $total_weight);
                    $this->datahelper->updateArticles($order_id, $consignmentNumber, $articleNumbers, $data, $content);
                       
                    $this->messageManager->addSuccess(__('The consignment has been created successfully.'));

                    // Magento 2.3 MSI related changes disable automated shipment generation.
                    
                    /* $mageShipment = $this->apimodel->generateMagentoShipment($order_id);
                    if ($mageShipment['status'] == 'success') {
                    } else {
                        $result = array('status' => 'error', 'message' => $mageShipment['message']);
                        $this->messageManager->addError(__($mageShipment['message']));
                    } */
                } else {
                    $error = __('createConsignment returned empty result');
                    $this->messageManager->addError($error);
                    $this->_redirect("sales/order/view", array('order_id' => $order_id, 'source' => 'grid', 'consignment_number' => $consignmentNumber));
                    return;
                }
            } catch (\Exception $e) {
                $error = __('Cannot create consignment, Error: ') . $e->getMessage();
                $this->messageManager->addError($error);
            }
        }
        $this->_redirect("sales/order/view", array('order_id' => $order_id, 'source' => 'grid', 'consignment_number' => $consignmentNumber));
    }
}
