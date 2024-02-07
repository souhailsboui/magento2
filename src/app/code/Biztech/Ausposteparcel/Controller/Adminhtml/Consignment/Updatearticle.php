<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Biztech\Ausposteparcel\Model\Consignment;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @property  messageManager
 */
class Updatearticle extends Action
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

        $orderWeight = $this->info->getOrderWeight($orderData);

        $articleWeight = $data['article']['weight'];
        $consignmentNumber = $data['consignment_number'];
        $articleNumber = $data['article_number'];
        $consignmentData = $this->info->getConsignment($order_id, $consignmentNumber);
        $totalArticles = count($this->info->getArticles($order_id, $consignmentNumber));
        $articleData = $this->info->getArticles($order_id, $consignmentNumber);
        $oldArticleData = $this->info->getArticle($order_id, $consignmentNumber, $articleNumber)->getData();
        $data['consignment_id'] = $consignmentData['consignment_id'];
        // $consignmentTotalWeight = $consignmentData['weight'] + $articleWeight - $oldArticleData['actual_weight'];
        $consignmentTotalWeight = 0;
        foreach ($articleData as $key => $value) {
            $consignmentTotalWeight += $value['actual_weight'];
        }
        $consignmentTotalWeight = $consignmentTotalWeight + $articleWeight - $oldArticleData['actual_weight'];
        $consignmentTotalWeight = number_format($consignmentTotalWeight, 2); 
        $orderWeight = number_format($orderWeight, 2);
        
        if ($consignmentTotalWeight > $orderWeight) {
            $error = (__('Combined consignment weight is more than the total order weight.'));
            $this->messageManager->addError($error);
            
            if ($data['source'] == 'grid') {
                $this->_redirect('*/consignment/editArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'article_number' => $this->getRequest()->getParam('article_number'), 'source' => 'grid'));
            } else {
                $this->_redirect('*/consignment/editArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'article_number' => $this->getRequest()->getParam('article_number')));
            }
        } else {
            try {
                $article = $this->info->getArticle($order_id, $consignmentNumber, $articleNumber);
                $article->setActualWeight(number_format($data['article']['weight'], 2));
                $article->setArticleDescription($data['article']['description']);
                $article->setHeight($data['article']['height']);
                $article->setIsTransitCoverRequired(($data['transit_cover_required']) ? "Y" : "N");
                $article->setTransitCoverAmount($data['transit_cover_amount']);
                $article->setLength($data['article']['length']);
                $article->setWidth($data['article']['width']);
                $article->setUnitValue($data['article']['unit_value']);

                $article->save();
                // $this->_redirect("sales/order/view", array('order_id' => $order_id, 'active_tab' => 'auspost_eparcel'));
                $this->messageManager->addSuccess(__('The article has been updated successfully.'));
                $this->_redirect("*/consignment/create", array('order_id' => $order_id, 'consignment_number' => $consignmentNumber));
                
                /*$articleData = $this->article->prepareUpdateArticleData($data, $orderData, $data['consignment_number']);
                $content = $articleData['content'];
                $chargeCode = $articleData['charge_code'];
                $total_weight = $articleData['total_weight'];
                $manifestNumber = $consignmentData['manifest_number'];*/
    
                /*if ($manifestNumber) {
                    if (isset($data['cash_to_collect']) && $data['cash_to_collect']) {
                        $cash_to_collect = 1;
                    } else {
                        $cash_to_collect = 0;
                    }

                    $articleNumbers = array();
                    for ($j = 0; $j < $totalArticles; $j++) {
                        $articleNumbers[$j] = '';
                        $articleNumbers[$j] .= $consignmentNumber;
                        if (strlen($j) > 1) {
                            $articleNumbers[$j] .= strval($j + 1);
                        } else {
                            $articleNumbers[$j] .= '0' . strval($j + 1);
                        }

                        $articleNumbers[$j] .= $this->info->getServiceCode($chargeCode, $data['delivery_signature_allowed'], $cash_to_collect, $data['print_return_labels'], $data['partial_delivery_allowed']);

                        if ($j == ($totalArticles - 1)) {
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

                    if ($data['print_labels']) {
                        // $this->datahelper->LabelCreate($order_id, $consignmentNumber);
                    }
                    if ($data['print_return_labels']) {
                        // $this->datahelper->returnLabelCreate($order_id, $consignmentNumber);
                    }
                    $this->messageManager->addSuccess(__('The article and consignment has been updated successfully.'));
                    if ($data['source'] == 'grid') {
                        // $this->_redirect("biztech_ausposteparcel/consignment/index", array('order_id' => $order_id, 'consignment_number' => $consignmentNumber));
                        $this->_redirect("sales/order/view", array('order_id' => $order_id, 'source' => 'grid'));
                    } else {
                        $this->_redirect("sales/order/view", array('order_id' => $order_id, 'active_tab' => 'auspost_eparcel'));
                    }
                }*/
            } catch (\Exception $e) {
                $error = __('Failed to update article and consignment. ') . $e->getMessage();
                $this->messageManager->addError($error);
                if ($data['source'] == 'grid') {
                    $this->_redirect('*/consignment/editArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'article_number' => $this->getRequest()->getParam('article_number'), 'source' => 'grid'));
                } else {
                    $this->_redirect('*/consignment/editArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'article_number' => $this->getRequest()->getParam('article_number')));
                }
            }
        }
    }
}
