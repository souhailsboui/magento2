<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Biztech\Ausposteparcel\Model\Consignment;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @property  messageManager
 */
class Updateconsignment extends Action
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
        $data = $this->getRequest()->getParams();
        $order_id = $data['order_id'];
        $orderData = $this->order->load($order_id);
        $number_of_articles = (int) trim($this->getRequest()->getParam('number_of_articles'));
        $data = $this->getRequest()->getParams();
        $data['start_index'] = 1;
        $data['end_index'] = $number_of_articles;
        try {
            $articleData = $this->article->prepareArticleData($data, $orderData, $data['consignment_number']);
            $orderWeight = $this->info->getOrderWeight($orderData);
            $content = $articleData['content'];
            $chargeCode = $articleData['charge_code'];
            $total_weight = $articleData['total_weight'];
            $consignmentNumber = $data['consignment_number'];
            $consignment = $this->info->getConsignment($order_id, $consignmentNumber);
            $manifestNumber = $consignment['manifest_number'];

            if ($total_weight > $orderWeight) {
                $error = (__('Combined consignment weight is more than the total order weight.'));
                $this->messageManager->addError($error);

                if ($data['source'] == 'grid') {
                    $this->_redirect('biztech_ausposteparcel/consignment/edit', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'source' => 'grid'));
                } else {
                    $this->_redirect('biztech_ausposteparcel/consignment/edit', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number')));
                }
            } else {
                if ($consignmentNumber) {
                    if (isset($data['cash_to_collect']) && $data['cash_to_collect']) {
                        $cash_to_collect = 1;
                    } else {
                        $cash_to_collect = 0;
                    }

                    $articleNumbers = array();
                    for ($j = 0; $j < $number_of_articles; $j++) {
                        $articleNumbers[$j] = '';
                        $articleNumbers[$j] .= $consignmentNumber;
                        if (strlen($j) > 1) {
                            $articleNumbers[$j] .= strval($j + 1);
                        } else {
                            $articleNumbers[$j] .= '0' . strval($j + 1);
                        }

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

                    $this->datahelper->updateConsignment($order_id, $consignmentNumber, $data, $manifestNumber, $chargeCode, $total_weight);
                    $this->datahelper->updateArticles($order_id, $consignmentNumber, $articleNumbers, $data, $content);
                    $success = (__($data['consignment_number'] .' consignment has been updated successfully.'));
                    $this->messageManager->addSuccess($success);
                    if ($data['source'] == 'grid') {
                        $this->_redirect("sales/order/view", array('order_id' => $order_id, 'consignment_number' => $consignmentNumber));
                    } else {
                        $this->_redirect("biztech_ausposteparcel/consignment/edit", array('order_id' => $order_id));
                    }
                }
            }
        } catch (\Exception $e) {
            $error = __('Cannot update consignment, Error: ') . $e->getMessage();
            $this->messageManager->addError($error);

            if ($data['source'] == 'grid') {
                $this->_redirect('biztech_ausposteparcel/consignment/edit', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'source' => 'grid'));
            } else {
                $this->_redirect('biztech_ausposteparcel/consignment/edit', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number')));
            }
        }
    }
}
